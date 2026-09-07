# Incident Assistant

## Visão do produto

O **Incident Assistant** é uma aplicação para registro, análise e investigação de incidentes técnicos relacionados a projetos de software.

O sistema centraliza informações sobre incidentes e utiliza Inteligência Artificial para auxiliar na triagem, investigação e consulta de conhecimento relacionado ao problema.

Além da interface da própria aplicação, o Incident Assistant deve disponibilizar seus dados e funcionalidades através de MCP, permitindo que agentes externos, como o Codex, consultem e interajam com incidentes enquanto trabalham diretamente no código de outros projetos.

---

## Objetivos

O Incident Assistant tem como principais objetivos:

* centralizar o registro de incidentes técnicos;
* organizar incidentes por projeto;
* preservar as informações originais fornecidas durante o registro de um incidente;
* utilizar IA para auxiliar na triagem inicial;
* permitir investigações assistidas por IA;
* manter um histórico das informações relevantes descobertas durante uma investigação;
* permitir que a IA consulte contexto adicional relacionado aos incidentes;
* disponibilizar incidentes para agentes externos através de MCP;
* permitir que agentes externos contribuam com a investigação sem criar uma lógica de negócio paralela à aplicação;

---

## Conceitos principais

### Project

Representa uma aplicação, serviço ou sistema que pode possuir incidentes.

Exemplos:

* Checkout API;
* Billing API;
* Meu PDV;
* Blog.

Um projeto funciona como o contexto principal para organização e consulta dos seus incidentes.

---

### Incident

Representa um problema técnico ocorrido em um projeto.

Um incidente pode conter informações como:

* título;
* descrição;
* logs ou stack traces;
* severidade;
* status;
* análise gerada por IA;
* histórico de investigação.

Todo incidente pertence a um projeto.

---

### Incident Note

Representa uma informação adicionada ao histórico de investigação de um incidente.

As notas podem ser registradas por diferentes origens, como:

* usuário;
* assistente de IA;
* agente externo através de MCP.

O conjunto de notas forma um histórico cronológico da investigação.

---

### AI Triage

É a análise inicial de um incidente realizada com auxílio de Inteligência Artificial.

A triagem pode produzir informações como:

* resumo do problema;
* severidade sugerida;
* possíveis causas;
* próximas ações recomendadas.

A análise gerada pela IA funciona como apoio à investigação e não substitui decisões humanas.

---

### Incident Assistant

É o assistente conversacional utilizado durante a investigação de um incidente.

O assistente recebe o contexto disponível e pode auxiliar o usuário a:

* compreender o problema;
* explorar possíveis causas;
* definir próximos passos;
* consultar incidentes relacionados;
* consultar conhecimentos e procedimentos disponíveis;
* registrar informações relevantes;
* propor alterações no incidente.

Ações que produzam alterações relevantes devem respeitar os mecanismos de controle definidos pela aplicação.

---

### MCP Server

O Incident Assistant disponibiliza parte de suas informações e operações através de um servidor MCP.

O objetivo é permitir que agentes externos utilizem o contexto dos incidentes durante outros fluxos de desenvolvimento.

Um agente trabalhando no repositório de uma aplicação poderá, por exemplo:

1. consultar um incidente;
2. analisar seu contexto;
3. relacionar o problema ao código-fonte;
4. investigar ou corrigir a causa;
5. registrar suas conclusões novamente no Incident Assistant.

---

## Fluxos principais

### Gerenciamento de projetos

O usuário pode cadastrar, consultar, editar e excluir os projetos utilizados para organizar os incidentes.

---

### Registro de incidentes

O usuário pode registrar manualmente um incidente relacionado a um projeto, fornecendo as informações disponíveis sobre o problema.

O incidente registrado passa a ser o ponto central para as etapas posteriores de análise e investigação.

---

### Triagem com IA

Um incidente pode ser analisado por IA para gerar uma triagem inicial.

A triagem deve utilizar os dados disponíveis no incidente sem alterar ou substituir as informações originais fornecidas pelo usuário.

O usuário continua responsável pelas decisões relacionadas ao incidente e pode ajustar informações sugeridas pela IA.

---

### Investigação

Durante a investigação, o usuário pode registrar notas e conversar com o assistente de IA.

O assistente deve utilizar o contexto disponível para fornecer respostas relacionadas ao incidente e pode recorrer a ferramentas e fontes adicionais quando necessário.

---

### Consulta de conhecimento

O assistente pode consultar documentação e procedimentos internos disponibilizados ao sistema.

Esse conhecimento complementa as informações do incidente e permite respostas contextualizadas ao ambiente no qual o problema ocorreu.

---

### Integração via MCP

Agentes externos podem consultar e interagir com incidentes através do servidor MCP.

Essa integração permite utilizar o Incident Assistant como fonte de contexto durante atividades realizadas fora da aplicação, especialmente durante investigação e manutenção de código.

---

## Severidade dos incidentes

Os incidentes podem ser classificados utilizando os seguintes níveis:

* `low`;
* `medium`;
* `high`;
* `critical`.

A severidade pode ser definida pelo usuário ou sugerida pela IA.

Sugestões realizadas pela IA não devem impedir alterações manuais.

---

## Status dos incidentes

Os incidentes possuem os seguintes estados principais:

* `open`;
* `investigating`;
* `resolved`.

Um incidente começa como aberto e pode evoluir conforme a investigação.

Incidentes resolvidos podem ser reabertos caso o problema volte a exigir investigação.

---

## Princípios do produto

### IA como apoio

A IA deve auxiliar usuários e agentes durante triagem e investigação, mas não deve ser tratada como autoridade absoluta sobre o incidente.

Informações geradas pela IA devem permanecer distinguíveis dos dados originais.

---

### Preservação do contexto original

Título, descrição, logs e demais informações fornecidas durante o registro de um incidente representam evidências importantes do problema.

Análises posteriores não devem modificar silenciosamente essas informações.

---

### Histórico da investigação

Informações relevantes descobertas durante a investigação devem poder ser registradas como parte do histórico do incidente.

Esse histórico deve permitir compreender como a investigação evoluiu ao longo do tempo.

---

### Controle sobre alterações

Operações propostas pela IA que alterem informações relevantes do incidente podem exigir aprovação humana antes de serem executadas.

---

### Regras de negócio compartilhadas

Interface web, recursos de IA e MCP representam diferentes formas de interação com o mesmo produto.

As mesmas regras de negócio devem ser respeitadas independentemente da origem da operação.

---

## Escopo inicial

O escopo inicial do produto contempla:

* projetos;
* incidentes;
* notas de investigação;
* classificação por severidade;
* acompanhamento do status;
* triagem de incidentes com IA;
* reanálise de incidentes;
* assistente conversacional para investigação;
* histórico de conversas;
* ferramentas utilizadas pelo assistente;
* aprovação humana para ações sensíveis;
* consulta de conhecimento contextual;
* respostas em streaming;
* servidor MCP;
* ferramentas MCP para consulta e interação com incidentes;
* recursos MCP para disponibilização de contexto;
* prompts MCP voltados à investigação;
* integração com agentes externos compatíveis com MCP.

---

## Fora do escopo inicial

Para manter o projeto pequeno e focado nos objetivos de estudo, não fazem parte do escopo inicial:

* organizações ou múltiplos tenants;
* gerenciamento complexo de equipes;
* autenticação e permissões avançadas;
* responsáveis por incidentes;
* SLAs;
* notificações;
* dashboards analíticos;
* relatórios avançados;
* labels ou sistemas complexos de categorização;
* integrações automáticas com ferramentas de monitoramento;
* integrações com Slack ou ferramentas semelhantes;
* abertura automática de incidentes;
* sistema completo de gerenciamento de documentação;
* funcionalidades equivalentes a plataformas comerciais de incident management.

Esses recursos podem ser considerados futuramente caso contribuam para novos objetivos de aprendizado ou evolução do produto.

---

## Evoluções possíveis

Após o escopo inicial, o produto poderá evoluir com funcionalidades como:

* criação de incidentes através de API ou webhook;
* integração com ferramentas de observabilidade;
* anexos contendo logs, arquivos ou screenshots;
* novas fontes de conhecimento;
* integrações MCP adicionais;
* utilização do Incident Assistant como cliente de outros servidores MCP;
* automações relacionadas ao ciclo de vida dos incidentes.

Essas possibilidades representam extensões futuras e não são requisitos para a primeira versão do produto.

---

## Critério de sucesso

O projeto será considerado bem-sucedido quando permitir executar um fluxo completo de investigação:

1. um projeto possui um incidente registrado;
2. a IA realiza uma triagem inicial;
3. o usuário investiga o problema com auxílio do assistente;
4. o assistente utiliza ferramentas e conhecimento adicional quando necessário;
5. informações relevantes são registradas no histórico;
6. um agente externo consegue consultar o incidente através de MCP;
7. o agente utiliza esse contexto enquanto trabalha no código do projeto relacionado;
8. as conclusões da investigação podem retornar ao Incident Assistant.

O objetivo não é reproduzir uma plataforma completa de gerenciamento de incidentes, mas criar uma aplicação pequena e funcional que permita à análise e investigação de incidentes técnicos com auxílio de IA.
