# Plano técnico — Projetos e incidentes

## Resumo técnico

Implementar a feature como um módulo web tradicional do Laravel 13 com Inertia 3, Vue 3, TypeScript, Tailwind CSS 4 e Wayfinder. O backend será responsável pelas regras de negócio, validação e persistência; o frontend receberá contratos explícitos e cuidará apenas da interação e apresentação.

A implementação seguirá TDD, controllers finos e Actions focadas. Não haverá autenticação, APIs públicas, IA, MCP ou abstrações preparatórias para features futuras.

## Arquitetura e componentes

- Criar os modelos `Project`, `Incident` e `IncidentNote`, com relacionamentos Eloquent tipados e factories.
- Representar severidade e status por enums PHP string-backed: `IncidentSeverity` e `IncidentStatus`.
- Centralizar todas as mutações em Actions com um único método público `handle`:
  - criação, atualização e exclusão de projetos;
  - criação, atualização e exclusão de incidentes;
  - início da investigação, resolução e reabertura;
  - inclusão de nota manual.
- Usar Form Requests separados para criação e edição de projetos e incidentes, inclusão de notas e validação dos filtros.
- Usar controllers resource para projetos e incidentes; notas e mudanças de estado terão controllers focados.
- Expor rotas nomeadas:
  - recursos `projects` e `incidents`;
  - `incidents.notes.store`;
  - `incidents.investigation.store`;
  - `incidents.resolution.store` e `incidents.resolution.destroy`.
- Redirecionar a rota inicial para a lista de projetos, primeiro requisito para cadastrar um incidente.
- Compartilhar mensagens flash de sucesso e erro pelo middleware do Inertia.
- Tratar transições inválidas e exclusão bloqueada com exceções de domínio não reportáveis, convertidas em feedback ao usuário sem alterar dados.
- Criar um layout administrativo com navegação para Projetos e Incidentes; páginas `Index`, `Create`, `Show` e `Edit` para ambos.
- Reutilizar apenas componentes concretamente repetidos: formulário de incidente, badges, paginação, estado vazio e mensagens flash.
- Usar `<Form>`, `<Link>` e ações geradas pelo Wayfinder, sem URLs manuais.
- Usar confirmação nativa do navegador para exclusões, evitando uma dependência ou sistema de modais fora do escopo.
- Aplicar a identidade visual registrada nas regras do projeto, incluindo badges textuais, logs em bloco monoespaçado e layout responsivo com prioridade para desktop.

## Modelo de dados / persistência

- `projects`: ID bigint, `name` com até 255 caracteres, `description` textual opcional e timestamps.
- `incidents`: ID bigint, `project_id` obrigatório, `title` com até 255 caracteres, `description` e `logs` longos opcionais, `severity`, `status` com padrão `open` e timestamps.
- `incident_notes`: ID bigint, `incident_id` obrigatório, `content` longo e timestamps; `created_at` representa o momento registrado.
- Usar colunas string para enums, mantendo valores restringidos pela aplicação e evitando enums nativos do MySQL.
- Impedir a exclusão de projetos relacionados por regra na Action e por chave estrangeira restritiva no banco.
- Excluir notas em cascata quando um incidente for excluído.
- Não usar soft deletes, `resolved_at`, autoria de notas ou histórico de mudanças de status, pois não são exigidos pela especificação.
- Indexar as colunas usadas nos filtros e relacionamentos; ordenar incidentes por `created_at` e `id` decrescentes e notas por ambos em ordem crescente.
- Paginar listas de projetos e incidentes em 15 itens, preservando filtros na query string.
- Listar projetos alfabeticamente nos seletores; não impor unicidade ao nome, pois a especificação não exige isso.

## Contratos técnicos

- Validar `name`, `title` e `content` como textos obrigatórios e não vazios; campos opcionais serão normalizados para `null`.
- Validar `project_id` contra um projeto existente e severidade/status com `Rule::enum`.
- Não aceitar `status` nos formulários de criação ou edição: ele será alterado exclusivamente pelas Actions de ciclo de vida.
- Aceitar filtros GET opcionais `project_id`, `severity` e `status`, combinados com semântica AND; valores inválidos serão rejeitados pela validação.
- Usar Resources do Laravel para limitar e estabilizar os props enviados ao Inertia:
  - resumos de projeto com `id`, `name`, `description` e `incidents_count`;
  - resumos de incidente com projeto, título, severidade, status e data;
  - detalhe do incidente com campos completos e notas;
  - opções de projeto, severidade e status no formato `{ value, label }`.
- Espelhar esses contratos em tipos TypeScript, com unions para os valores dos enums e nulabilidade explícita.
- Usar a estrutura paginada padrão do Laravel (`data`, `links` e `meta`).
- Manter filtros na URL e atualizá-los por visita GET do Inertia; limpar filtros remove somente os três parâmetros suportados.
- Apresentar textos e rótulos em português e datas no fuso/configuração da aplicação, formatadas para `pt-BR` na interface.
- Não criar endpoints JSON ou contratos públicos além das rotas web Inertia.

## Estratégia de testes

- Criar testes Pest de feature para renderização das páginas, contratos Inertia, estados vazios, validação e persistência de cada operação.
- Cobrir criação, edição e exclusão de projetos, inclusive bloqueio quando houver incidentes.
- Cobrir criação e edição de incidentes, associação e transferência entre projetos, exclusão com cascata das notas e status inicial `open`.
- Cobrir todas as transições permitidas e rejeitar transições incompatíveis sem mudança no banco.
- Cobrir inclusão de notas, conteúdo vazio, notas em incidentes resolvidos e ordenação cronológica estável.
- Cobrir filtros isolados e combinados, limpeza, ausência de resultados, paginação e preservação da query string.
- Usar datasets para valores válidos e inválidos dos enums e factories com estados nomeados para severidades e status.
- Testar comportamento observável e contratos, sem testes de navegador ou dependências adicionais.
- Durante a implementação, executar primeiro os testes específicos de cada fluxo e, ao final, `php artisan test --compact`, `vendor/bin/pint --dirty --format agent`, análise PHPStan, verificação do frontend, tipos TypeScript e build.

## Dependências e integrações

- Utilizar somente Laravel, Inertia, Vue, TypeScript, Tailwind CSS, Wayfinder e Pest já instalados no projeto.
- Não adicionar pacotes PHP ou JavaScript.
- Não criar integrações externas, endpoints de API, recursos de IA ou componentes MCP.
- Não criar seeders de demonstração; as factories existirão exclusivamente como suporte aos testes e ao desenvolvimento.

## Riscos e decisões relevantes

- IDs bigint seguem a convenção já existente e evitam antecipar requisitos de identificação externa.
- Enums PHP com colunas string preservam tipagem no domínio sem acoplar a evolução dos valores ao tipo `ENUM` do MySQL.
- Exclusões serão definitivas porque restauração e arquivamento não pertencem à especificação; a confirmação na interface reduz exclusões acidentais.
- A restrição dupla para exclusão de projetos mantém uma mensagem compreensível na aplicação e garante integridade no banco.
- As mudanças de status possuem endpoints e Actions separados para impedir alterações arbitrárias pelo formulário de edição.
- Resources explícitos evitam expor atributos Eloquent acidentalmente e estabilizam os contratos do frontend.
- A paginação tradicional mantém URLs navegáveis e atende ao volume crescente sem introduzir infinite scroll.
- A confirmação nativa atende ao requisito sem criar uma infraestrutura de modais não justificada nesta feature.
