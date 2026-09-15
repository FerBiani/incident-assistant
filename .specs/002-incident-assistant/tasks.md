# Tarefas — Assistente de incidentes

## Regras de execução

- Antes de implementar, reler `.specs/product.md`, `spec.md`, `plan.md` e este `tasks.md`, nessa ordem.
- Considerar autoritativa a regra atual da especificação que permite consultar incidentes relacionados: `AddIncidentNote` e `SearchRelatedIncidents` devem permanecer disponíveis no `IncidentAgent`.
- Preservar o isolamento da conversa e do contexto principal por incidente; dados de outro incidente só podem entrar na investigação como resultado explícito de `SearchRelatedIncidents`.
- Antes de criar ou editar arquivos, consultar `.ai/rules/index.md`, ler todas as regras aplicáveis aos caminhos envolvidos e pesquisar palavras-chave relacionadas em `.ai/rules`.
- Consultar a documentação versionada com Laravel Boost antes de usar APIs de Laravel, Laravel AI SDK, Inertia, Wayfinder, Tailwind ou Pest.
- Criar arquivos Laravel com `php artisan make:* --no-interaction` sempre que houver um gerador apropriado.
- Aplicar TDD aos comportamentos de backend: escrever o teste, confirmar a falha esperada, implementar o mínimo necessário e executar novamente o teste específico.
- Manter controllers restritos a entrada HTTP, delegação e resposta; concentrar operações de negócio em Actions com um único método público `handle`.
- Usar Resources para contratos enviados ao Inertia e manter seus tipos TypeScript sincronizados.
- Usar Wayfinder em todas as chamadas do frontend para rotas Laravel, inclusive na requisição `fetch` do stream.
- Não adicionar dependências, WebSockets, Markdown, autenticação, RAG, MCP, anexos, notificações ou funcionalidades ausentes da especificação.
- Depois de modificar PHP, executar `vendor/bin/pint --dirty --format agent` antes de concluir cada incremento.
- Fazer commits Conventional Commits em inglês somente para unidades coerentes e verdes, preservando alterações não relacionadas.

## Fase 1 — Estados de análise e origem das notas

- [x] **RED:** criar testes de persistência para os estados `not_started`, `pending`, `completed` e `failed`, incluindo cast do enum e compatibilidade de incidentes existentes com e sem análise concluída.
- [x] **RED:** criar testes para as origens `user` e `ai`, verificando cast, valor padrão e serialização textual das duas origens.
- [x] **GREEN:** criar os enums string-backed `IncidentAnalysisStatus` e `IncidentNoteSource` com exatamente os valores definidos no plano.
- [x] **GREEN:** criar migration para adicionar `ai_analysis_status` com padrão `not_started` e marcar como `completed` os incidentes que já possuam `ai_analyzed_at`.
- [x] **GREEN:** criar migration para normalizar origens existentes para `user` e `ai`, aplicar o padrão `user` e tornar `source` obrigatória.
- [x] **GREEN:** atualizar models, casts, atributos preenchíveis, PHPDoc e factories para refletir os novos enums e estados.
- [x] **GREEN:** atualizar `IncidentNoteResource` para expor `source` e `source_label` sem depender da cor para comunicar a origem.
- [x] **GREEN:** executar novamente os testes de persistência e contratos de notas até passarem.
- [x] **REFACTOR:** revisar migrations e casts para manter compatibilidade com o banco configurado e evitar estados textuais duplicados fora dos enums.

## Fase 2 — Triagem assíncrona e reanálise

- [x] **RED:** atualizar os testes de criação de incidente para verificar persistência imediata, estado `pending`, despacho do Job e ausência de chamada síncrona ao `IncidentTriageAgent`.
- [x] **RED:** criar testes para solicitação manual de reanálise, recusa de uma segunda solicitação enquanto houver análise pendente e feedback correspondente.
- [x] **RED:** criar testes do Job comprovando que uma resposta válida substitui somente os campos da análise, define `completed` e preserva título, descrição, logs, severidade, status e notas.
- [x] **RED:** criar testes de falha e timeout comprovando estado `failed`, preservação da última análise concluída e permanência do incidente criado.
- [x] **GREEN:** criar uma Action focada em solicitar análise, fazendo a transição atômica para `pending`, despachando o Job e impedindo solicitações concorrentes.
- [x] **GREEN:** criar o Job que executa `AnalyzeIncident`, com timeout superior ao do agente, falha em timeout e tratamento de falha definitiva.
- [x] **GREEN:** adaptar `AnalyzeIncident` para marcar `completed` junto à atualização atômica do resultado, sem limpar previamente a análise anterior.
- [x] **GREEN:** integrar a solicitação automática em `CreateIncident`, tratando falha de despacho sem desfazer o cadastro.
- [x] **GREEN:** adaptar `IncidentAnalysisController` para iniciar a reanálise em segundo plano, retornar imediatamente e apresentar feedback de início, duplicidade ou falha de despacho.
- [x] **GREEN:** ajustar `retry_after` da fila e o exemplo de ambiente para permanecer acima do timeout do Job.
- [x] **GREEN:** executar os testes de criação, solicitação, Job e Action de análise até todos passarem.
- [x] **REFACTOR:** revisar a coordenação concorrente e garantir que somente a transição válida para `pending` possa despachar trabalho.

## Fase 3 — Conversa persistente e streaming SSE

- [x] **RED:** criar testes HTTP para a primeira mensagem, verificando criação de uma conversa cujo participante é o incidente, persistência cronológica e resposta `text/event-stream` em deltas.
- [x] **RED:** criar testes para mensagens posteriores, garantindo reutilização da conversa mais recente daquele incidente e inclusão do histórico anterior no prompt.
- [x] **RED:** criar teste de isolamento que comprove que uma conversa nunca é continuada usando o participante ou o histórico de outro incidente.
- [x] **RED:** criar testes de validação para mensagem vazia, envio simultâneo de mensagem e decisões e envio de mensagem enquanto houver aprovação pendente.
- [x] **GREEN:** criar o Form Request do endpoint conversacional com os formatos mutuamente exclusivos de `message` e `decisions`.
- [x] **GREEN:** criar uma Action que resolva a conversa exclusivamente pelo relacionamento do incidente, inicie-a com `forParticipant` ou continue a mais recente e retorne o stream do `IncidentAgent`.
- [x] **GREEN:** criar o controller conversacional e registrar `POST /incidents/{incident}/conversation` com o nome `incidents.conversation`.
- [x] **GREEN:** manter `AddIncidentNote` e `SearchRelatedIncidents` no `IncidentAgent`, sem adicionar novas Tools.
- [x] **GREEN:** desabilitar a geração automática de títulos de conversa no SDK, mantendo o histórico e a associação ao incidente.
- [x] **GREEN:** garantir que o stream exponha os eventos nativos necessários e finalize com `[DONE]`, sem broadcasting ou protocolo adicional.
- [x] **GREEN:** executar os testes do endpoint, persistência, continuidade, isolamento e streaming até todos passarem.
- [x] **REFACTOR:** revisar os limites entre Request, controller, Action e agente, removendo acesso a conversas por IDs fornecidos pelo cliente.

## Fase 4 — Tools, incidentes relacionados e aprovação humana

- [x] **RED:** criar testes da disponibilidade das duas Tools no `IncidentAgent` e do contexto atual do incidente fornecido às duas instâncias.
- [x] **RED:** criar testes de `SearchRelatedIncidents` comprovando consulta intencional a incidentes relacionados, exclusão do incidente atual e ausência de alteração em qualquer registro.
- [x] **RED:** criar teste em que `AddIncidentNote` produz uma aprovação pendente e comprovar que nenhuma nota é criada antes da decisão.
- [x] **RED:** criar testes para aprovação, rejeição, IDs desconhecidos, conjunto incompleto de decisões, decisão repetida e falha da Tool após aprovação.
- [x] **GREEN:** adaptar `AddIncidentNote` para delegar a mutação à Action existente com origem `IncidentNoteSource::Ai`, mantendo a aprovação humana obrigatória e uma descrição compreensível em português.
- [x] **GREEN:** adaptar a Action de notas para aceitar uma origem explícita, usando `IncidentNoteSource::User` como padrão para o fluxo manual existente.
- [x] **GREEN:** converter decisões validadas em `Decisions`/`Decision` do SDK; rejeições devem incluir a justificativa fixa em português para permitir a continuação do agente.
- [x] **GREEN:** validar que todas e somente as aprovações ainda pendentes da conversa receberam uma decisão antes de retomar o stream.
- [x] **GREEN:** preservar `SearchRelatedIncidents` como Tool somente de leitura, sem aprovação e sem transformar sua consulta em RAG ou busca semântica.
- [x] **GREEN:** executar os testes das Tools e de todas as transições de aprovação até passarem.
- [x] **REFACTOR:** revisar a separação entre consulta e mutação, garantindo que somente `AddIncidentNote` exija aprovação e que nenhuma decisão autorize chamadas adicionais.

## Fase 5 — Contratos do detalhe e histórico

- [x] **RED:** ampliar os testes Inertia do detalhe para os quatro estados da análise, conteúdo completo da última análise, origens das notas e conversa ausente ou existente.
- [x] **RED:** criar testes de serialização das mensagens em ordem cronológica e das ações com estados `pending`, `approved`, `rejected` e `failed`.
- [x] **RED:** criar teste de exclusão do incidente que comprove a remoção de suas conversas e mensagens sem afetar conversas de outros incidentes.
- [x] **GREEN:** ampliar `IncidentResource` com o objeto estável `analysis`, incluindo estado, conteúdo, severidade sugerida e rótulo, listas e timestamp.
- [x] **GREEN:** criar Resources para a conversa, mensagens e aprovações, interpretando `tool_calls`, `tool_results` e `approval_state` persistidos pelo SDK.
- [x] **GREEN:** carregar somente a conversa mais recente do incidente e ordenar mensagens e notas por `created_at` e `id` crescentes.
- [x] **GREEN:** mapear chamadas aprováveis para descrições compreensíveis; resultados de `SearchRelatedIncidents` não devem ser apresentados como ações pendentes.
- [x] **GREEN:** adaptar a exclusão do incidente para remover, dentro da mesma operação, suas mensagens e conversas antes do próprio incidente.
- [x] **GREEN:** executar os testes de Resources, detalhe, ordenação e exclusão até todos passarem.
- [x] **REFACTOR:** revisar eager loading, quantidade de consultas e formato dos props, sem expor metadados internos desnecessários do SDK.

## Fase 6 — Painel de triagem no frontend

- [x] Atualizar os tipos TypeScript para análise, estado da análise e origem das notas, refletindo exatamente os Resources e sua nulabilidade.
- [x] Criar o componente de triagem com estados `not_started`, `pending`, `completed` e `failed`, identificação de conteúdo gerado por IA e ação manual de análise/reanálise.
- [x] Exibir resumo, severidade sugerida, causas prováveis, próximas ações e data da análise, preservando o resultado anterior durante processamento ou falha.
- [x] Conectar a ação manual ao controller existente por Wayfinder e desabilitar o controle enquanto houver análise pendente.
- [x] Usar `usePoll` com recarga parcial apenas enquanto o estado for `pending`, iniciando e interrompendo o polling conforme as mudanças do prop.
- [x] Integrar o painel à página do incidente com layout responsivo, estados e feedback coerentes com as regras visuais existentes.
- [x] Executar os testes HTTP relacionados, `npm run check` e `npm run types:check`; corrigir erros antes de avançar.

## Fase 7 — Chat, streaming e aprovações no frontend

- [x] Atualizar os tipos TypeScript para conversa, mensagens, aprovações, decisões e eventos SSE suportados.
- [x] Adicionar o token CSRF ao template raiz e criar o consumo do endpoint com `fetch`, URL gerada pelo Wayfinder e leitura incremental via `ReadableStream`.
- [x] Implementar o parser dos frames SSE, acumulando `text_delta`, detectando aprovações e resultados, tratando `error` e encerrando em `[DONE]` sem interpretar conteúdo como HTML.
- [x] Criar o componente de chat com histórico cronológico, distinção textual entre usuário e assistente, formulário de mensagem e resposta temporária progressiva.
- [x] Manter mensagens anteriores e deltas já recebidos visíveis quando o stream falhar, apresentando um estado compreensível de interrupção.
- [x] Exibir cada ação pendente no turno correspondente, incluindo descrição e argumentos relevantes, com controles explícitos de aprovação e rejeição.
- [x] Para múltiplas aprovações no mesmo passo, coletar uma decisão para cada ação e enviar o conjunto somente quando estiver completo.
- [x] Desabilitar mensagens e decisões durante geração, impedir nova mensagem enquanto houver pendências e remover controles de ações já decididas.
- [x] Após `[DONE]` ou falha terminal, executar recarga parcial da conversa e das notas para sincronizar mensagens, decisões e notas eventualmente criadas.
- [x] Atualizar o histórico de notas para mostrar badges textuais “Usuário” e “IA” com tratamento visual distinto e ordem cronológica estável.
- [x] Integrar o chat à página do incidente, mantendo todos os fluxos utilizáveis em telas pequenas e a prioridade visual de desktop.
- [x] Regenerar as funções Wayfinder e confirmar que o endpoint conversacional e a análise são chamados sem URLs Laravel manuais.
- [x] Executar `npm run check`, `npm run types:check` e `npm run build` até todas as verificações passarem.

## Fase 8 — Integração e acabamento

- [x] Executar o fluxo completo com fakes: criar incidente, observar análise pendente, concluir triagem, solicitar reanálise e confirmar substituição somente após sucesso.
- [x] Executar o fluxo completo da conversa: enviar mensagem, receber deltas, consultar incidente relacionado por Tool, solicitar nota, rejeitar e continuar, solicitar novamente, aprovar e sincronizar a nota de IA.
- [x] Confirmar que falhas de triagem, stream e Tool preservam os dados e históricos exigidos e deixam a interface pronta para continuar ou tentar novamente quando permitido.
- [x] Confirmar que as informações originais, severidade e status do incidente nunca são modificados pela triagem ou pelo chat.
- [x] Revisar textos, estados, labels e datas em português/`pt-BR`, além de acessibilidade textual e responsividade.
- [x] Revisar que nenhum fluxo adicionou RAG, MCP, busca semântica, anexos, Markdown, autenticação, notificações ou novas mutações de incidente.
- [x] Executar novamente os testes específicos após qualquer ajuste de acabamento e manter cada incremento verde.

## Verificação final

- [x] Executar `vendor/bin/pint --dirty --format agent` e revisar somente os ajustes de estilo esperados.
- [x] Executar `php artisan test --compact` e confirmar que toda a suíte passa.
- [x] Executar a análise PHPStan configurada pelo projeto.
- [x] Executar `npm run check`, `npm run types:check` e `npm run build`.
- [x] Revisar os testes conforme a skill de boas práticas, confirmando comportamento observável, cenários distintos, dados determinísticos, isolamento dos agentes e assertions específicas.
- [x] Revisar a implementação contra `product.md`, `spec.md`, `plan.md` e este `tasks.md`, garantindo cobertura de todos os critérios de aceitação.
- [x] Confirmar que `AddIncidentNote` e `SearchRelatedIncidents` permanecem disponíveis no `IncidentAgent` e que somente a primeira exige aprovação.
- [x] Confirmar que o worker de fila utilizado no ambiente possui timeout compatível com o Job e que `retry_after` permanece superior a esse limite.
- [x] Revisar o diff e o status do Git, preservando alterações anteriores e removendo somente artefatos criados indevidamente durante esta feature.
- [x] Confirmar que todos os commits da feature são unidades coerentes, passam suas verificações relevantes e usam mensagens Conventional Commits em inglês.

## Definition of Done

A feature só estará concluída quando todas as tarefas deste documento estiverem marcadas, os comportamentos e critérios de aceitação de `002-incident-assistant/spec.md` possuírem cobertura automatizada, as verificações PHP e frontend estiverem verdes, os fluxos de triagem, conversa, consulta de incidentes relacionados e aprovação funcionarem de ponta a ponta, e não houver alterações fora do escopo da feature.
