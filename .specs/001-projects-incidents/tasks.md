# Tarefas — Projetos e incidentes

## Regras de execução

- Antes de implementar, reler `.specs/product.md`, `spec.md`, `plan.md` e este `tasks.md`, nessa ordem.
- Antes de iniciar a implementação, consultar `.ai/rules/index.md` e ler as regras aplicáveis aos arquivos, tecnologias e áreas envolvidas. Durante a execução, consultar regras adicionais ao entrar em caminhos ou contextos ainda não cobertos.
- Consultar a documentação versionada com Laravel Boost antes de usar APIs de Laravel, Inertia, Wayfinder, Tailwind ou Pest.
- Criar arquivos Laravel com comandos `php artisan make:* --no-interaction` sempre que existir um gerador apropriado.
- Aplicar TDD aos comportamentos e regras de negócio: escrever o teste, confirmar a falha esperada, implementar o mínimo necessário e executar novamente o teste específico.
- Manter controllers limitados a entrada HTTP, delegação para Actions e resposta; cada Action deve representar um caso de uso e expor somente `handle` como operação pública.
- Usar somente dados validados e manter os contratos de Resources e tipos TypeScript sincronizados.
- Usar Wayfinder em toda navegação e submissão do frontend, sem URLs Laravel escritas manualmente.
- Não adicionar autenticação, API pública, IA, RAG, Agents, Tools, MCP, anexos, labels, dashboards ou qualquer comportamento ausente de `spec.md`.
- Não adicionar dependências nem seeders de demonstração.
- Depois de modificar PHP, executar `vendor/bin/pint --dirty --format agent` antes de considerar o incremento concluído.
- Fazer commits convencionais incrementais apenas com unidades coerentes e verdes, sem incluir alterações não relacionadas.

## Fase 1 — Fundação do domínio e persistência

- [x] **RED:** criar testes de comportamento para persistência dos três recursos, relacionamentos obrigatórios, status inicial `open`, casts dos enums, bloqueio da exclusão de projeto relacionado e cascata das notas ao excluir um incidente; executar os testes e confirmar que falham pela ausência do domínio.
- [x] **GREEN:** criar os enums string-backed `IncidentSeverity` e `IncidentStatus` com exatamente os valores definidos na especificação.
- [x] **GREEN:** criar migrations para `projects`, `incidents` e `incident_notes` com IDs bigint, campos, nulabilidade, timestamps, chaves estrangeiras, políticas de exclusão e índices definidos no plano.
- [x] **GREEN:** criar os modelos `Project`, `Incident` e `IncidentNote` com atributos preenchíveis, casts e relacionamentos Eloquent tipados.
- [x] **GREEN:** criar factories para os três modelos, incluindo estados nomeados de severidade e status para incidentes e associações explícitas entre os registros.
- [x] **GREEN:** executar novamente os testes da fundação e confirmar os comportamentos de persistência e integridade.
- [x] **REFACTOR:** revisar nomes, tipos, PHPDoc, índices e factories, removendo duplicações sem alterar os contratos cobertos.

## Fase 2 — Fluxos backend de projetos

- [x] **RED:** criar testes HTTP para listagem paginada e alfabética com `incidents_count`, estado vazio, criação, visualização, edição, validação de nome e exclusão de projeto sem incidentes; confirmar as falhas esperadas.
- [x] **RED:** adicionar o cenário de exclusão bloqueada para projeto com incidentes, verificando resposta, mensagem compreensível e permanência do projeto no banco.
- [x] **GREEN:** criar Form Requests de criação e atualização de projetos, normalizando a descrição opcional para `null` e aceitando somente os campos previstos.
- [x] **GREEN:** criar as Actions focadas de criação, atualização e exclusão de projetos e a exceção de domínio não reportável para exclusão bloqueada.
- [x] **GREEN:** criar o Resource de projeto com contrato explícito e inclusão condicional de `incidents_count` e incidentes quando carregados.
- [x] **GREEN:** criar o controller resource de projetos, suas rotas nomeadas e o redirecionamento da raiz para `projects.index`.
- [x] **GREEN:** preparar consultas com contagem, paginação de 15 itens, ordenação alfabética e carregamento explícito dos incidentes necessários à página de detalhe.
- [x] **GREEN:** compartilhar flashes `success` e `error` pelo middleware do Inertia e converter o bloqueio de exclusão em retorno com feedback, sem persistência parcial.
- [x] **GREEN:** executar os testes de projetos até todos passarem.
- [x] **REFACTOR:** revisar limites entre controller, Action, Request e Resource, garantindo consultas sem N+1 e ausência de regras de projeto na camada Vue.

## Fase 3 — CRUD, relacionamento e filtros de incidentes

- [x] **RED:** criar testes HTTP para orientação quando não há projetos, criação com status `open`, visualização completa, edição, mudança de projeto e exclusão definitiva com cascata das notas.
- [x] **RED:** cobrir validação dos campos obrigatórios, projeto inexistente, severidades inválidas, limites textuais e ausência de `status` como campo editável pelo CRUD.
- [x] **RED:** criar testes da listagem geral para ordenação estável, paginação, filtros isolados por `project_id`, `severity` e `status`, combinação AND, query string preservada, filtros inválidos e resultado vazio.
- [x] **RED:** criar teste da listagem no detalhe do projeto para garantir que somente incidentes daquele projeto sejam enviados.
- [x] **GREEN:** criar Form Requests de criação, atualização e filtros, usando `Rule::enum`, validação de projeto existente e normalização dos textos opcionais para `null`.
- [x] **GREEN:** criar Actions focadas de criação, atualização e exclusão de incidentes, preservando notas durante edições e deixando o status fora dos dados editáveis.
- [x] **GREEN:** criar Resources de resumo e detalhe de incidente, incluindo somente projeto, campos, notas e timestamps necessários a cada página.
- [x] **GREEN:** criar o controller resource e as rotas nomeadas de incidentes, com eager loading, filtros condicionais, ordenação por `created_at`/`id` decrescente e paginação de 15 itens com query string.
- [x] **GREEN:** disponibilizar seletores de projeto em ordem alfabética e opções `{ value, label }` derivadas dos enums.
- [x] **GREEN:** executar os testes de CRUD, relacionamento e filtros até todos passarem.
- [x] **REFACTOR:** consolidar scopes somente onde houver reutilização real, revisar o formato dos Resources e eliminar carregamentos ou atributos desnecessários.

## Fase 4 — Ciclo de vida e notas de investigação

- [x] **RED:** criar testes para iniciar investigação somente a partir de `open`, resolver a partir de `open` ou `investigating` e reabrir somente a partir de `resolved`.
- [x] **RED:** para cada transição inválida, verificar mensagem de erro, ausência de mudança no banco e preservação integral dos dados e notas.
- [x] **RED:** criar testes para adicionar nota manual válida em qualquer status, rejeitar conteúdo vazio e registrar o timestamp de forma determinística.
- [x] **RED:** criar teste do detalhe do incidente que comprove ordenação das notas por `created_at` e `id` crescentes e contrato sem ações de edição ou exclusão individual.
- [x] **GREEN:** criar as Actions `StartIncidentInvestigation`, `ResolveIncident`, `ReopenIncident` e `AddIncidentNote`, cada uma com apenas o método público `handle`.
- [x] **GREEN:** criar a exceção de transição inválida como não reportável e garantir que o controller a converta em feedback sem alterar o incidente.
- [x] **GREEN:** criar o Form Request de nota e os controllers focados de investigação, resolução e notas.
- [x] **GREEN:** registrar as rotas `incidents.investigation.store`, `incidents.resolution.store`, `incidents.resolution.destroy` e `incidents.notes.store`.
- [x] **GREEN:** executar os testes de ciclo de vida e notas até todos passarem.
- [x] **REFACTOR:** revisar a matriz de transições, garantir que não exista endpoint genérico para status e remover qualquer duplicação sem ampliar o ciclo previsto.

## Fase 5 — Contratos frontend e estrutura visual compartilhada

- [x] Atualizar os tipos TypeScript para projetos, incidentes, notas, enums, opções, paginação e flashes, refletindo exatamente os Resources e sua nulabilidade.
- [x] Gerar ou atualizar as funções tipadas do Wayfinder após a definição das rotas e confirmar que variantes de formulário e query parameters estão disponíveis.
- [x] Criar o layout administrativo com fundo, superfícies, tipografia, navegação para Projetos e Incidentes, área principal responsiva e exibição dos flashes compartilhados.
- [x] Criar componentes reutilizados de estado vazio, paginação, badge de severidade e badge de status, mantendo texto como identificação principal e as cores definidas nas regras do projeto.
- [x] Garantir que links de paginação preservem filtros e que componentes tratem corretamente links indisponíveis.
- [x] Executar a checagem de frontend e de tipos; corrigir erros antes de avançar.

## Fase 6 — Interface de projetos

- [x] Criar a listagem de projetos com nome, descrição quando presente, quantidade de incidentes, paginação, estado vazio e ação de cadastro.
- [x] Criar os formulários de cadastro e edição com campos previstos, indicação de obrigatoriedade, dados preservados após erro, mensagens adjacentes e bloqueio durante processamento.
- [x] Criar o detalhe do projeto com seus dados, incidentes restritos ao projeto e acessos para consultar ou cadastrar incidentes no contexto disponível.
- [x] Conectar criação, edição, navegação e exclusão às funções Wayfinder; exigir confirmação nativa antes da exclusão e apresentar o feedback de bloqueio retornado pelo backend.
- [x] Validar manualmente os estados vazio, sucesso, validação, falha e bloqueio de exclusão em tamanhos desktop e pequeno, sem alterar regras de negócio no cliente.
- [x] Executar os testes HTTP de projetos, checagem de frontend e tipos até todos passarem.

## Fase 7 — Interface de incidentes, filtros e investigação

- [x] Criar a listagem geral com título, projeto, severidade, status, paginação e os estados vazios distintos para ausência de incidentes e ausência de correspondências.
- [x] Criar filtros por projeto, severidade e status que atualizem a URL por visita GET do Inertia, combinem os valores e ofereçam limpeza dos três parâmetros.
- [x] Criar os formulários compartilhados de cadastro e edição, sem campo de status, com seletor de projeto, severidade e campos opcionais previstos.
- [x] Quando não houver projetos, substituir o fluxo de cadastro por orientação e ação para cadastrar o primeiro projeto.
- [x] Criar o detalhe do incidente com todos os campos, badges textuais, ausência tratada para opcionais e logs/stack traces em bloco escuro monoespaçado, com whitespace preservado e rolagem horizontal.
- [x] Exibir somente ações válidas para o status atual e conectá-las aos endpoints Wayfinder de investigação, resolução e reabertura.
- [x] Criar o histórico cronológico de notas e o formulário de inclusão manual, sem controles para editar ou excluir notas e sem bloquear notas em incidentes resolvidos.
- [x] Conectar exclusão do incidente com confirmação nativa e retorno apropriado à listagem.
- [x] Validar manualmente navegação, filtros, paginação, formulários, transições, notas, feedback e responsividade, mantendo a interface utilizável em telas pequenas.
- [x] Executar os testes HTTP de incidentes e notas, checagem de frontend, tipos e build até todos passarem.

## Fase 8 — Integração e acabamento

- [x] Confirmar que todos os formulários e links internos usam Wayfinder e componentes do Inertia, sem URLs manuais ou submissões HTML tradicionais.
- [x] Confirmar que os dados exibidos provêm dos Resources, que entradas são escapadas pelo Vue e que logs são apresentados como texto, nunca como HTML interpretado.
- [x] Revisar consultas de todas as páginas para evitar N+1, atributos textuais pesados em listagens e ordenação não determinística.
- [x] Revisar mensagens, labels e datas em português/`pt-BR`, incluindo feedback de sucesso, erro, validação e estados vazios.
- [x] Executar os testes específicos após qualquer ajuste de acabamento e manter cada incremento verde.

## Verificação final

- [x] Executar `vendor/bin/pint --dirty --format agent` e revisar apenas ajustes de estilo esperados.
- [x] Executar `php artisan test --compact` e confirmar que toda a suíte passa.
- [x] Executar a análise PHPStan configurada pelo projeto.
- [x] Executar a checagem do frontend, a verificação de tipos TypeScript e o build de produção.
- [x] Revisar os testes conforme a checklist da skill de boas práticas, confirmando comportamento observável, cenários distintos, dados determinísticos e assertions específicas.
- [x] Revisar a implementação contra `product.md`, `spec.md` e `plan.md`, garantindo cobertura de todos os critérios de aceitação.
- [x] Confirmar que não foram adicionados autenticação, API, IA, RAG, Agents, Tools, MCP, dependências, seeders ou abstrações futuras.
- [x] Revisar o diff e o status do Git, preservando alterações anteriores e removendo somente artefatos criados indevidamente pela implementação desta feature.
- [x] Confirmar que todos os commits da feature são unidades coerentes, passam suas verificações relevantes e usam mensagens Conventional Commits em inglês.

## Definition of Done

A feature só estará concluída quando todas as tarefas deste documento estiverem marcadas, todos os comportamentos da especificação possuírem cobertura automatizada, a suíte completa e as verificações de PHP e frontend estiverem verdes, a interface atender aos fluxos e estados descritos, e não houver alterações fora do escopo de `001-projects-incidents`.
