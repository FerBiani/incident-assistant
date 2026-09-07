# Feature — Projetos e incidentes

## Visão geral

Esta feature estabelece o fluxo manual básico do Incident Assistant. Ela permite organizar incidentes técnicos por projeto, acompanhar sua severidade e seu estado, registrar o contexto original do problema e manter notas cronológicas da investigação.

Todo o conteúdo desta feature é criado e administrado diretamente pelo usuário. Recursos de inteligência artificial, assistência conversacional, consulta de conhecimento, agentes, ferramentas e MCP serão tratados em features posteriores e não fazem parte deste escopo.

## Objetivos

- Permitir o cadastro, a consulta, a edição e a exclusão de projetos.
- Permitir o cadastro, a consulta, a edição e a exclusão de incidentes associados a projetos.
- Preservar e apresentar as informações fornecidas pelo usuário sobre cada incidente.
- Permitir a classificação dos incidentes por severidade e o acompanhamento de seu status.
- Permitir que incidentes sejam marcados como resolvidos e reabertos quando necessário.
- Permitir o registro manual de descobertas em um histórico cronológico de investigação.
- Permitir a localização de incidentes por filtros básicos.
- Disponibilizar uma interface web suficiente para executar esses fluxos.

## Histórias de usuário

### Gerenciamento de projetos

- Como usuário, quero cadastrar um projeto para organizar os incidentes de uma aplicação, serviço ou sistema.
- Como usuário, quero consultar os projetos existentes e a quantidade de incidentes de cada um para compreender onde os problemas estão organizados.
- Como usuário, quero visualizar os dados de um projeto e seus incidentes para acessar seu contexto operacional.
- Como usuário, quero editar um projeto para manter suas informações atualizadas.
- Como usuário, quero excluir um projeto sem incidentes para remover cadastros que não são mais necessários.

### Gerenciamento de incidentes

- Como usuário, quero registrar um incidente em um projeto informando o contexto disponível para iniciar seu acompanhamento.
- Como usuário, quero consultar uma lista de incidentes com seus dados principais para identificar rapidamente os problemas registrados.
- Como usuário, quero visualizar todos os dados de um incidente e seu histórico de investigação em um único lugar.
- Como usuário, quero editar os dados de um incidente para corrigir ou complementar informações de forma intencional.
- Como usuário, quero excluir um incidente quando seu registro não precisar ser mantido.
- Como usuário, quero filtrar incidentes por projeto, severidade e status para reduzir a lista aos itens relevantes.

### Ciclo de vida e investigação

- Como usuário, quero indicar que um incidente está em investigação para refletir o trabalho em andamento.
- Como usuário, quero marcar um incidente como resolvido quando o problema não exigir mais investigação ativa.
- Como usuário, quero reabrir um incidente resolvido quando o problema voltar a exigir investigação.
- Como usuário, quero adicionar notas manuais ao incidente para registrar descobertas e decisões durante a investigação.
- Como usuário, quero ler as notas em ordem cronológica para compreender como a investigação evoluiu.

## Regras de negócio

### Projetos

1. Um projeto representa uma aplicação, serviço ou sistema usado como contexto para incidentes.
2. Um projeto deve possuir um nome não vazio.
3. Um projeto pode possuir uma descrição opcional.
4. O nome e a descrição de um projeto podem ser alterados pelo usuário.
5. Um projeto que possua incidentes não pode ser excluído.
6. Ao bloquear a exclusão de um projeto, a aplicação deve informar que seus incidentes precisam ser excluídos ou associados a outro projeto antes da exclusão.

### Incidentes e relacionamento com projetos

1. Todo incidente deve pertencer a exatamente um projeto existente.
2. Um projeto pode possuir nenhum, um ou vários incidentes.
3. Um incidente deve possuir título não vazio, severidade e status.
4. Um incidente pode possuir descrição e logs ou stack traces.
5. No cadastro, o usuário deve informar o projeto, o título, a severidade e, quando disponíveis, a descrição e os logs ou stack traces.
6. Todo novo incidente deve iniciar com status `open`.
7. O usuário pode editar o projeto associado, o título, a descrição, os logs ou stack traces e a severidade do incidente.
8. Alterações manuais nos dados do incidente devem ocorrer somente após uma ação explícita do usuário e não podem modificar suas notas existentes.
9. A exclusão de um incidente deve exigir confirmação e também excluir as notas pertencentes exclusivamente a ele.

### Severidade

1. A severidade de um incidente deve ser exatamente uma das seguintes:
   - `low`;
   - `medium`;
   - `high`;
   - `critical`.
2. A severidade é definida manualmente pelo usuário nesta feature.
3. A severidade pode ser alterada enquanto o incidente existir, inclusive depois de resolvido.

### Status, resolução e reabertura

1. O status de um incidente deve ser exatamente um dos seguintes:
   - `open`;
   - `investigating`;
   - `resolved`.
2. Um incidente `open` pode ser colocado em investigação ou resolvido.
3. Um incidente `investigating` pode ser resolvido.
4. Resolver um incidente deve alterar seu status para `resolved` sem remover ou alterar seus dados e notas.
5. Somente um incidente `resolved` pode ser reaberto.
6. Reabrir um incidente deve alterar seu status para `open` sem remover ou alterar seus dados e notas.
7. A interface deve tornar explícitas as ações de iniciar investigação, resolver e reabrir, exibindo apenas as ações válidas para o status atual.

### Notas de investigação

1. Uma nota de investigação pertence a exatamente um incidente.
2. Nesta feature, as notas são adicionadas manualmente pelo usuário.
3. Uma nota deve possuir conteúdo textual não vazio e a data e hora em que foi registrada.
4. As notas devem ser apresentadas em ordem cronológica, da mais antiga para a mais recente.
5. Novas notas devem ser acrescentadas ao histórico sem alterar as notas anteriores.
6. Notas não podem ser editadas ou excluídas individualmente nesta feature.
7. É permitido adicionar uma nota a um incidente em qualquer status, inclusive `resolved`.

### Filtros de incidentes

1. A lista geral de incidentes deve permitir filtros por projeto, severidade e status.
2. Cada filtro deve ser opcional.
3. Quando mais de um filtro estiver selecionado, somente incidentes que atendam a todos eles devem ser exibidos.
4. O usuário deve conseguir limpar os filtros e retornar à lista completa.
5. A ausência de resultados deve ser apresentada como um estado vazio, sem ser tratada como erro.
6. A lista de incidentes dentro de um projeto deve permanecer restrita àquele projeto.

### Interface

1. A aplicação deve possuir navegação clara para as áreas de projetos e incidentes.
2. A área de projetos deve oferecer:
   - listagem com nome e quantidade de incidentes;
   - estado vazio com ação para cadastrar o primeiro projeto;
   - formulário de criação;
   - visualização de um projeto com seus dados e incidentes;
   - formulário de edição;
   - ação de exclusão com confirmação e feedback quando ela estiver bloqueada.
3. A área de incidentes deve oferecer:
   - listagem geral com título, projeto, severidade e status;
   - filtros por projeto, severidade e status;
   - estado vazio com ação de cadastro quando houver ao menos um projeto;
   - orientação para cadastrar um projeto antes do primeiro incidente quando não houver projetos;
   - formulário de criação;
   - visualização detalhada com projeto, título, descrição, logs ou stack traces, severidade, status e notas;
   - formulário de edição;
   - ações de status válidas para o estado atual;
   - ação de exclusão com confirmação.
4. A visualização detalhada do incidente deve separar visualmente logs e stack traces do restante do conteúdo, preservando sua formatação e permitindo a leitura de linhas longas.
5. O histórico de investigação e o formulário para adicionar uma nota devem estar disponíveis na visualização detalhada do incidente.
6. Severidade e status devem ser identificáveis por texto; a cor pode reforçar a identificação, mas não pode ser o único meio de distingui-los.
7. Formulários devem indicar os campos obrigatórios e apresentar erros de validação junto aos respectivos campos.
8. Operações concluídas, falhas de operação e listas sem conteúdo devem produzir feedback visível e compreensível.
9. Os fluxos principais devem permanecer utilizáveis em telas pequenas, ainda que a experiência priorize desktop.

## Critérios de aceitação

### Projetos

1. **Dado** que não existem projetos, **quando** o usuário acessa a lista de projetos, **então** ele vê um estado vazio e uma ação para cadastrar o primeiro projeto.
2. **Dado** um nome válido e uma descrição opcional, **quando** o usuário cadastra um projeto, **então** o projeto passa a aparecer na listagem e pode ser consultado.
3. **Dado** um projeto existente, **quando** o usuário altera seus dados com valores válidos, **então** os novos dados são exibidos nas consultas seguintes.
4. **Dado** um projeto sem incidentes, **quando** o usuário confirma sua exclusão, **então** ele deixa de aparecer na aplicação.
5. **Dado** um projeto com pelo menos um incidente, **quando** o usuário tenta excluí-lo, **então** a exclusão é recusada e a aplicação explica como remover o impedimento.

### Incidentes

1. **Dado** que não existe projeto, **quando** o usuário tenta iniciar o cadastro de um incidente, **então** a aplicação orienta que um projeto deve ser cadastrado primeiro.
2. **Dado** um projeto existente, **quando** o usuário cadastra um incidente com título e severidade válidos, **então** o incidente é associado ao projeto e criado com status `open`.
3. **Dado** um incidente existente, **quando** o usuário abre seus detalhes, **então** ele vê o projeto, o título, a descrição, os logs ou stack traces, a severidade, o status e o histórico de notas, respeitando a ausência de campos opcionais.
4. **Dado** um incidente existente, **quando** o usuário edita seus dados com valores válidos, **então** as alterações são persistidas sem modificar as notas já registradas.
5. **Dado** um incidente e outro projeto existente, **quando** o usuário altera o projeto associado, **então** o incidente passa a aparecer no contexto do novo projeto e deixa de aparecer no contexto do anterior.
6. **Dado** um incidente existente, **quando** o usuário confirma sua exclusão, **então** o incidente e suas notas deixam de aparecer na aplicação.

### Severidade e status

1. **Dado** o cadastro ou a edição de um incidente, **quando** a severidade informada não é `low`, `medium`, `high` ou `critical`, **então** a operação é recusada com erro de validação.
2. **Dado** um incidente `open`, **quando** o usuário inicia sua investigação, **então** o status passa a ser `investigating`.
3. **Dado** um incidente `open` ou `investigating`, **quando** o usuário o resolve, **então** o status passa a ser `resolved` e seus dados e notas permanecem disponíveis.
4. **Dado** um incidente `resolved`, **quando** o usuário o reabre, **então** o status passa a ser `open` e todo o histórico permanece disponível.
5. **Dado** um incidente que não está resolvido, **quando** sua interface é exibida, **então** a ação de reabertura não é oferecida.

### Notas de investigação

1. **Dado** um incidente existente, **quando** o usuário adiciona uma nota com conteúdo válido, **então** ela é registrada com data e hora e aparece no histórico.
2. **Dado** um conteúdo vazio, **quando** o usuário tenta adicionar uma nota, **então** a operação é recusada com erro de validação.
3. **Dado** um incidente com várias notas, **quando** o usuário consulta seus detalhes, **então** as notas são exibidas da mais antiga para a mais recente.
4. **Dado** um incidente resolvido, **quando** o usuário adiciona uma nota manual, **então** a nota é registrada sem alterar o status do incidente.
5. **Dado** uma nota registrada, **quando** o usuário consulta o histórico, **então** não são oferecidas ações para editá-la ou excluí-la individualmente.

### Filtros e estados da interface

1. **Dado** um conjunto de incidentes, **quando** o usuário seleciona um projeto, uma severidade ou um status, **então** somente os incidentes correspondentes ao filtro são exibidos.
2. **Dado** mais de um filtro selecionado, **quando** a lista é atualizada, **então** são exibidos somente os incidentes que atendem simultaneamente a todos os filtros.
3. **Dado** filtros ativos, **quando** o usuário os limpa, **então** a lista completa volta a ser exibida.
4. **Dado** um conjunto de filtros sem correspondências, **quando** a lista é exibida, **então** o usuário vê um estado vazio apropriado e pode limpar os filtros.
5. **Dado** qualquer severidade ou status exibido, **quando** o usuário consulta uma lista ou um detalhe, **então** o valor pode ser identificado por texto independentemente da cor.
6. **Dado** um erro de validação ou uma falha de operação, **quando** a aplicação responde à ação, **então** o usuário recebe feedback compreensível e os dados já preenchidos no formulário são preservados quando aplicável.

## Fora de escopo

- Triagem ou reanálise de incidentes com IA.
- Sugestão automática de severidade, status, causas ou próximas ações.
- Assistente conversacional e histórico de conversas.
- Notas criadas por IA, agentes externos ou qualquer origem diferente do usuário.
- RAG, embeddings, bancos vetoriais, consulta de conhecimento ou gerenciamento de documentação.
- Agents, Tools, tool calling ou automações de investigação.
- Servidor, ferramentas, recursos, prompts ou aplicações MCP.
- Aprovação humana de ações propostas por IA.
- Respostas em streaming.
- API pública, webhooks, abertura automática de incidentes e integrações com observabilidade ou comunicação.
- Autenticação, organizações, múltiplos tenants, equipes, permissões, responsáveis por incidentes ou atribuições.
- SLAs, notificações, escalonamento, dashboards analíticos e relatórios avançados.
- Labels, categorias adicionais, comentários encadeados, anexos, screenshots ou arquivos.
- Edição ou exclusão individual de notas de investigação.
- Funcionalidades equivalentes a uma plataforma completa de gerenciamento de incidentes.
