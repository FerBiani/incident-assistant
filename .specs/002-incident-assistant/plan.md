# Plano técnico — Assistente de incidentes

## Resumo técnico

Integrar a camada de IA existente à página de detalhes do incidente usando Laravel 13, Laravel AI SDK, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4 e Wayfinder.

A triagem será executada por uma fila durável. O chat usará streaming SSE direto da resposta do `IncidentAgent`, sem WebSockets ou novas dependências. Conversas, mensagens, chamadas de ferramentas e aprovações continuarão armazenadas nas tabelas fornecidas pelo Laravel AI SDK.

## Arquitetura e componentes

### Triagem

- Manter `AnalyzeIncident` responsável por executar o `IncidentTriageAgent` e persistir o resultado estruturado.
- Criar uma Action para solicitar análises, alterando atomicamente o estado para `pending` e despachando um Job. Uma solicitação concorrente enquanto já houver análise pendente será recusada.
- Executar o Job com limite de tempo superior aos 120 segundos configurados no agente, falha em timeout e estado `failed` após falha definitiva.
- Acionar a Action dentro de `CreateIncident`, garantindo que qualquer fluxo de criação inicie a triagem automaticamente.
- Adaptar o controller de análise existente para somente solicitar a reanálise e retornar imediatamente.
- Preservar a análise concluída anterior enquanto uma reanálise estiver pendente ou falhar; somente um novo resultado bem-sucedido substituirá seus campos.
- Configurar `retry_after` da fila acima do timeout do Job para impedir processamento simultâneo da mesma reserva.
- Na página do incidente, usar polling parcial do Inertia somente enquanto o estado da análise for `pending`, interrompendo-o ao chegar a `completed` ou `failed`.

### Chat e aprovação

- Criar um controller e uma Action focados em iniciar ou continuar a conversa associada ao incidente.
- Usar uma única conversa contínua por incidente: a primeira mensagem chama `forParticipant($incident)` e as seguintes continuam a conversa mais recente pertencente ao mesmo incidente.
- Expor `POST /incidents/{incident}/conversation`, nomeada `incidents.conversation`, aceitando uma mensagem ou um conjunto de decisões de aprovação.
- Retornar diretamente o stream SSE nativo do Laravel AI SDK e consumir no Vue com `fetch` e `ReadableStream`. Não adicionar o pacote JavaScript da Vercel nem infraestrutura de broadcasting.
- Processar no cliente os eventos `text_delta`, `tool_approval_request`, `tool_result`, `error` e `[DONE]`. Os deltas formarão uma resposta temporária progressiva; ao terminar, uma recarga parcial sincronizará conversa e notas persistidas.
- Desabilitar novos envios enquanto houver geração ou aprovações pendentes. Se o agente produzir várias aprovações no mesmo passo, apresentar todas e retomar a conversa somente depois que cada uma tiver uma decisão, conforme exige o SDK.
- Enviar rejeições ao agente com uma justificativa fixa em português para que ele registre a recusa e continue a conversa.
- Resolver sempre a conversa por meio do relacionamento do incidente. O cliente não poderá escolher um `conversation_id` arbitrário.
- Validar no servidor que os IDs decididos correspondem exatamente às aprovações ainda pendentes; mensagens comuns serão recusadas enquanto existir uma pendência.
- Desabilitar a geração automática de títulos de conversa pelo SDK, pois existe apenas uma conversa por incidente e o título não será apresentado.

### Interface

- Extrair da página componentes dedicados para o painel de triagem e para o chat, mantendo na página a composição geral e os fluxos já existentes.
- Apresentar resumo, severidade sugerida, causas prováveis, ações recomendadas e data da última análise, sempre identificados como conteúdo de IA.
- Representar os estados `not_started`, `pending`, `completed` e `failed`; incidentes anteriores sem análise começarão como `not_started` e oferecerão análise manual.
- Durante uma reanálise, manter o resultado anterior visível junto ao indicador de atualização.
- Renderizar mensagens como texto simples com preservação de quebras de linha, sem Markdown ou HTML gerado pelo modelo.
- Exibir ações de aprovação dentro do turno correspondente, incluindo descrição, conteúdo proposto, estado e eventual falha.
- Atualizar a lista de notas após uma ferramenta aprovada e ordenar todas as notas por `created_at` e `id`, da mais antiga para a mais recente.
- Identificar notas por texto e tratamento visual: “Usuário” para notas manuais e “IA” para notas criadas pelo assistente, sem depender somente de cor.
- Usar Wayfinder em todas as chamadas às rotas Laravel e adicionar o token CSRF ao template raiz para as requisições `fetch`.

## Modelo de dados / persistência

- Criar `IncidentAnalysisStatus`, string-backed, com `NotStarted`, `Pending`, `Completed` e `Failed`.
- Adicionar `ai_analysis_status` aos incidentes com padrão `not_started`. A migration marcará registros com `ai_analyzed_at` existente como `completed`.
- Manter os campos atuais de análise como a única versão persistida; nenhum histórico de análises será criado.
- Criar `IncidentNoteSource`, string-backed, com `User` e `Ai`.
- Normalizar valores existentes de origem para `user` e `ai`, definir `user` como padrão e tornar a coluna obrigatória.
- Adaptar a Action existente de criação de notas para receber a origem, com `User` como padrão. A Tool delegará a ela usando `Ai`, eliminando a escrita direta feita atualmente pela Tool.
- Ao excluir um incidente pela Action existente, remover também todas as conversas e mensagens pertencentes a ele para não manter histórico órfão.

## Contratos técnicos

- O detalhe do incidente passará a expor:
  - `analysis`: estado, resumo, severidade sugerida e rótulo, causas prováveis, ações recomendadas e data da análise;
  - notas com `source` e `source_label`;
  - `conversation`, nula quando ainda não iniciada, contendo ID e mensagens cronológicas.
- Cada mensagem exporá ID, papel `user` ou `assistant`, conteúdo, data e ações de aprovação relacionadas.
- Cada aprovação exporá ID da chamada, ferramenta, descrição, argumentos relevantes, resultado e estado `pending`, `approved`, `rejected` ou `failed`.
- O endpoint conversacional aceitará exclusivamente um dos formatos:
  - `{ "message": "texto" }`;
  - `{ "decisions": { "<tool-call-id>": { "action": "approve|reject" } } }`.
- Mensagem e decisões serão mutuamente exclusivas. Conteúdo vazio, decisões desconhecidas, incompletas ou já resolvidas produzirão erro de validação sem executar ferramentas.
- Os tipos TypeScript espelharão esses contratos e os enums de análise, origem, mensagem e aprovação.
- Nenhuma API pública será criada; trata-se de um endpoint web interno com resposta `text/event-stream`.

## Estratégia de testes

- Testar que a criação persiste imediatamente o incidente, marca a análise como pendente e despacha o Job sem executar o agente na requisição.
- Testar solicitação manual, bloqueio de solicitações simultâneas e preservação da análise anterior durante processamento ou falha.
- Testar o Job com o fake do `IncidentTriageAgent`: sucesso substitui a análise e marca `completed`; exceção marca `failed` sem apagar o último resultado.
- Testar os contratos Inertia para todos os estados da triagem, dados completos da análise e polling condicionado no frontend por verificações de tipo e build.
- Testar início e continuação da conversa com `IncidentAgent::fake()`, isolamento entre incidentes, ordem do histórico e resposta SSE em múltiplos deltas.
- Testar validação do endpoint para mensagem vazia, combinação proibida de mensagem e decisões, conversa de outro incidente e mensagem enviada durante aprovação pendente.
- Testar aprovação e rejeição com respostas falsas do SDK, garantindo que a Tool não execute antes da aprovação, que rejeição não crie nota e que aprovação crie exatamente uma nota de origem `ai`.
- Testar aprovações repetidas, incompletas ou desconhecidas e falha da Tool sem apagar a decisão ou o histórico anterior.
- Testar serialização e apresentação das origens `user` e `ai`, além da ordenação cronológica estável das notas.
- Executar os testes Pest afetados, depois `php artisan test --compact`, Pint, PHPStan, `npm run check`, `npm run types:check` e `npm run build`.

## Dependências, decisões e limites

- Não adicionar pacotes PHP ou JavaScript. O Laravel AI SDK instalado já oferece streaming SSE, participantes de conversa, persistência e retomada de aprovações, conforme a [documentação oficial](https://laravel.com/framework/docs/13.x/ai-sdk).
- Usar polling para o estado da triagem porque não existe infraestrutura de WebSocket configurada e o requisito não exige atualização push.
- Manter apenas a conversa mais recente do incidente visível e reutilizá-la continuamente; não haverá criação, seleção ou exclusão manual de conversas.
- Conservar a análise anterior em reanálises malsucedidas evita perda de informação sem criar histórico de versões.
- Respostas parciais interrompidas permanecerão na interface atual com indicação de falha; após recarregar, será exibido somente o histórico que o SDK conseguiu persistir.
- O ambiente deverá manter um worker de fila ativo. O fluxo local existente de desenvolvimento já inicia o listener; ambientes implantados deverão executar worker compatível com o timeout documentado.
- Não incluir RAG, MCP, anexos, Markdown, notificações ou qualquer nova mutação do incidente.
