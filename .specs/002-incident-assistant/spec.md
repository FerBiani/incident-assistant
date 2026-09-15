# Feature — Assistente de incidentes

## Visão geral

Esta feature adiciona assistência por inteligência artificial à triagem e à investigação de incidentes. Após o registro de um incidente, a aplicação inicia automaticamente uma análise em segundo plano e apresenta seu resultado nos detalhes do incidente. O usuário também pode solicitar uma nova análise quando precisar atualizar a triagem.

Na mesma página, o usuário pode conversar com um assistente que utiliza o contexto daquele incidente, preserva o histórico da investigação e apresenta as respostas progressivamente. Quando uma ação proposta pelo assistente exigir aprovação humana, o usuário mantém o controle sobre sua execução por meio de uma decisão explícita de aprovação ou rejeição.

Esta especificação descreve somente os comportamentos observáveis da feature. A forma de reutilizar ou adaptar componentes e capacidades já existentes será definida posteriormente no `plan.md`.

## Objetivos

- Iniciar automaticamente a triagem por IA após a criação de um incidente, sem atrasar a conclusão do cadastro.
- Disponibilizar a análise gerada na página do incidente.
- Permitir que o usuário solicite uma reanálise e que o resultado mais recente substitua a análise anterior.
- Oferecer um assistente conversacional contextualizado dentro da página do incidente.
- Preservar o histórico da conversa como parte da investigação do incidente.
- Apresentar as respostas do assistente progressivamente enquanto são geradas.
- Manter ações sensíveis sob controle humano, sem executá-las antes de uma aprovação explícita.
- Diferenciar visualmente notas criadas pela IA das notas adicionadas manualmente.

## Histórias de usuário

### Triagem do incidente

- Como usuário, quero que um incidente recém-criado seja analisado automaticamente para receber apoio inicial de triagem sem precisar iniciar essa análise manualmente.
- Como usuário, quero concluir o cadastro e continuar usando a aplicação enquanto a análise é produzida em segundo plano.
- Como usuário, quero consultar a análise na página do incidente para compreender o resumo, as possíveis causas e os próximos passos sugeridos.
- Como usuário, quero solicitar uma nova análise para considerar o contexto atualizado do incidente.
- Como usuário, quero identificar quando uma análise está em andamento ou não pôde ser concluída para compreender o estado atual da triagem.

### Assistente conversacional para investigação

- Como usuário, quero conversar com o assistente dentro da página do incidente para investigar o problema sem perder seu contexto.
- Como usuário, quero que o assistente considere as informações disponíveis do incidente para receber respostas relacionadas ao caso em investigação.
- Como usuário, quero retomar uma conversa anterior para preservar a continuidade da investigação.
- Como usuário, quero acompanhar a resposta enquanto ela é gerada para receber seu conteúdo progressivamente.
- Como usuário, quero aprovar ou rejeitar ações sensíveis solicitadas pelo assistente para manter controle sobre alterações no incidente.
- Como usuário, quero continuar a conversa depois de decidir sobre uma ação pendente para prosseguir com a investigação.
- Como usuário, quero distinguir notas criadas pela IA de notas manuais para compreender a origem de cada informação do histórico.

## Regras de negócio

### Triagem automática

1. A criação bem-sucedida de um incidente deve iniciar automaticamente sua triagem por IA.
2. A triagem deve ocorrer em segundo plano e não pode bloquear a conclusão do cadastro nem o acesso ao incidente criado.
3. A triagem deve utilizar o contexto disponível do incidente ao ser iniciada.
4. A análise gerada deve ser associada somente ao incidente que forneceu seu contexto.
5. A análise deve apoiar a investigação sem alterar o título, a descrição, os logs ou stack traces, a severidade, o status ou as notas fornecidas pelo usuário.
6. Enquanto ainda não houver um resultado, a página do incidente deve indicar que a análise está em andamento.
7. Uma falha na triagem não pode desfazer nem invalidar a criação do incidente.
8. Quando a triagem não puder ser concluída, a página do incidente deve apresentar um estado compreensível e permitir que o usuário solicite uma nova análise.

### Consulta e reanálise

1. A análise concluída deve ficar disponível na página do respectivo incidente.
2. A análise deve ser apresentada como conteúdo gerado por IA e permanecer distinguível das informações originais fornecidas pelo usuário.
3. O usuário deve poder solicitar manualmente uma nova análise dentro da página do incidente.
4. A reanálise deve considerar o contexto disponível do incidente no momento da nova solicitação.
5. Solicitar uma reanálise não deve bloquear o uso da página do incidente enquanto o novo resultado é produzido.
6. Enquanto uma reanálise estiver em andamento, a interface deve tornar esse estado visível.
7. Quando uma reanálise for concluída com sucesso, seu resultado deve substituir a análise anterior apresentada para o incidente.
8. Esta feature mantém apenas a análise concluída mais recente; não é oferecido um histórico de versões das análises.
9. Uma reanálise não pode alterar silenciosamente as informações originais, a severidade, o status ou as notas do incidente.

### Conversa contextualizada

1. O chat deve estar disponível dentro da página do incidente ao qual a investigação se refere.
2. Cada mensagem enviada pelo usuário deve ser tratada no contexto do incidente aberto.
3. O contexto utilizado pelo assistente pode incluir os dados atuais do incidente, sua análise disponível, suas notas e a conversa preservada daquele incidente.
4. Incidentes relacionados podem ser buscados e consultados para auxiliar na resolução do incidente em questão.
5. As respostas do assistente devem ser apresentadas progressivamente conforme forem geradas.
6. Durante a geração, a interface deve indicar que a resposta ainda está em andamento.
7. O conteúdo já recebido deve permanecer visível caso a geração seja interrompida, e a interface deve comunicar que a resposta não foi concluída.
8. Uma falha ao gerar uma resposta não pode remover mensagens anteriores da conversa.

### Histórico da conversa

1. As mensagens do usuário e do assistente devem ser preservadas e vinculadas ao respectivo incidente.
2. Ao sair da página e retornar ao incidente, o usuário deve encontrar o histórico já registrado.
3. As mensagens devem ser apresentadas em ordem cronológica e com identificação clara de quem enviou cada uma.
4. Uma nova mensagem deve ser acrescentada ao histórico sem alterar as mensagens anteriores.
5. Decisões de aprovação ou rejeição e o resultado decorrente delas devem permanecer compreensíveis no fluxo da conversa.

### Aprovação humana de ações

1. Quando o assistente solicitar uma ação que exija aprovação humana, a interface deve apresentar essa ação como pendente na conversa.
2. A ação pendente deve descrever de forma compreensível o que o assistente pretende realizar.
3. O usuário deve poder aprovar ou rejeitar explicitamente a ação pendente.
4. Uma ação sujeita a aprovação não pode ser executada enquanto permanecer pendente.
5. A rejeição deve encerrar a pendência sem executar a ação.
6. A aprovação deve autorizar a execução da ação apresentada, sem autorizar ações diferentes ou adicionais.
7. Após a aprovação ou a rejeição, a decisão deve ficar visível e a conversa deve poder continuar normalmente.
8. A interface não deve continuar oferecendo uma ação já decidida como se ainda estivesse pendente.
9. Se uma ação aprovada não puder ser concluída, a aplicação deve informar a falha na conversa sem ocultar a aprovação já concedida.

### Notas criadas pela IA

1. O assistente (ou o usuário) pode solicitar a criação de uma nota de investigação por IA durante a conversa.
2. Quando a criação dessa nota exigir aprovação humana, ela deve seguir integralmente as regras de aprovação desta feature.
3. Uma nota criada pela IA deve ser acrescentada ao mesmo histórico cronológico de investigação das notas manuais.
4. Notas criadas pela IA devem possuir uma identificação textual e visual clara de sua origem.
5. Notas manuais devem continuar identificadas como conteúdo do usuário e não podem ser apresentadas como conteúdo gerado por IA.
6. A diferenciação de origem não pode depender somente de cor.
7. A criação de uma nota pela IA não pode alterar as notas anteriores nem o status do incidente.

### Interface e feedback

1. A página do incidente deve reunir os dados já existentes do incidente, sua triagem, o histórico de notas e o chat de investigação.
2. Os estados de análise em andamento, análise concluída e falha na análise devem ser reconhecíveis pelo usuário.
3. Os estados de resposta em geração, ação pendente, ação aprovada, ação rejeitada e falha de ação devem produzir feedback visível e compreensível.
4. Controles indisponíveis durante uma operação em andamento devem comunicar seu estado e não podem induzir o usuário a acreditar que uma nova operação foi iniciada.
5. Os fluxos de triagem, conversa e aprovação devem permanecer utilizáveis em telas pequenas, ainda que a experiência priorize desktop.

## Critérios de aceitação

### Triagem automática e reanálise

1. **Dado** um novo incidente com dados válidos, **quando** o cadastro é concluído, **então** o incidente é criado e sua triagem por IA é iniciada automaticamente em segundo plano.
2. **Dado** que a triagem automática ainda está em andamento, **quando** o cadastro termina, **então** o usuário pode acessar e utilizar o incidente sem aguardar a conclusão da análise.
3. **Dado** um incidente em análise, **quando** o usuário abre sua página, **então** ele vê que a triagem está em andamento.
4. **Dado** uma triagem concluída, **quando** o usuário consulta o incidente, **então** a análise gerada é exibida como conteúdo de IA sem substituir as informações originais do incidente.
5. **Dado** que a triagem automática falhou, **quando** o usuário consulta o incidente, **então** o incidente permanece disponível, a falha é comunicada e uma nova análise pode ser solicitada.
6. **Dado** um incidente existente, **quando** o usuário solicita uma nova análise, **então** a reanálise é iniciada em segundo plano com o contexto atual e a página informa que ela está em andamento.
7. **Dado** um incidente com análise anterior, **quando** a reanálise é concluída com sucesso, **então** o novo resultado substitui o anterior e apenas a análise mais recente é apresentada.
8. **Dado** uma triagem ou reanálise concluída, **quando** seus efeitos são observados, **então** os dados originais, a severidade, o status e as notas do incidente não foram modificados pela análise.

### Conversa e streaming

1. **Dado** um incidente existente, **quando** o usuário acessa seus detalhes, **então** ele encontra o chat de investigação dentro da mesma página.
2. **Dado** que o usuário envia uma mensagem no chat de um incidente, **quando** o assistente responde, **então** a resposta considera o contexto daquele incidente e não incorpora dados ou conversas de outro incidente.
3. **Dado** que uma resposta está sendo gerada, **quando** novos trechos ficam disponíveis, **então** eles são apresentados progressivamente e a interface indica que a geração ainda não terminou.
4. **Dado** uma resposta concluída, **quando** o usuário retorna posteriormente à página do incidente, **então** sua mensagem e a resposta do assistente permanecem no histórico em ordem cronológica.
5. **Dado** um histórico existente, **quando** o usuário envia uma nova mensagem, **então** a conversa continua com o contexto anterior preservado e sem alterar mensagens já registradas.
6. **Dado** que a geração de uma resposta é interrompida ou falha, **quando** a interface apresenta o resultado, **então** as mensagens anteriores e o conteúdo já recebido permanecem visíveis e a interrupção é comunicada.

### Aprovação humana

1. **Dado** que o assistente solicita uma ação sujeita a aprovação, **quando** a solicitação aparece na conversa, **então** ela é exibida como pendente, descreve a ação e oferece opções de aprovação e rejeição.
2. **Dado** uma ação pendente, **quando** o usuário ainda não tomou uma decisão, **então** a ação não é executada.
3. **Dado** uma ação pendente, **quando** o usuário a rejeita, **então** ela não é executada, a rejeição fica visível e a conversa pode continuar.
4. **Dado** uma ação pendente, **quando** o usuário a aprova, **então** somente a ação apresentada é autorizada, seu resultado fica visível e a conversa pode continuar.
5. **Dado** uma ação já aprovada ou rejeitada, **quando** o histórico é exibido, **então** sua decisão permanece identificável e os controles não permitem decidir novamente a mesma pendência.
6. **Dado** uma ação aprovada que falha durante a execução, **quando** o resultado é apresentado, **então** a falha é comunicada sem apagar o registro da aprovação e a conversa pode continuar.

### Origem das notas

1. **Dado** que o assistente propõe criar uma nota sujeita a aprovação, **quando** o usuário ainda não a aprovou, **então** a nota não aparece no histórico de investigação como criada.
2. **Dado** que o usuário aprova a criação de uma nota proposta pelo assistente, **quando** a ação é concluída, **então** a nota é acrescentada ao histórico cronológico do incidente e identificada como criada pela IA.
3. **Dado** que o usuário rejeita a criação de uma nota proposta pelo assistente, **quando** a decisão é registrada, **então** nenhuma nota é criada e a conversa pode continuar.
4. **Dado** um histórico contendo notas manuais e notas criadas pela IA, **quando** ele é exibido, **então** a origem de cada nota pode ser distinguida por texto e por tratamento visual, sem depender somente de cor.

## Fora de escopo

- RAG, embeddings, bancos vetoriais, ingestão, indexação ou busca semântica de documentos.
- Consulta ou gerenciamento de bases de conhecimento e procedimentos internos.
- Servidor, ferramentas, recursos, prompts, aplicações ou integrações MCP.
- Interação com agentes externos ou registro de notas por MCP.
- Novos tipos de severidade, status, campos ou fluxos de ciclo de vida do incidente.
- Alteração automática de severidade, status ou informações originais do incidente a partir da triagem.
- Histórico de versões de análises ou comparação entre análises anteriores.
- Conversas fora do contexto de um incidente.
- Autenticação, permissões, organizações, equipes ou múltiplos tenants.
- Anexos, imagens, áudio ou outros tipos de mensagem além de conteúdo textual e solicitações de ação.
- Notificações externas sobre conclusão de análises, respostas ou ações.
- Funcionalidades adicionais de gerenciamento de projetos, incidentes e notas além das já definidas para o escopo inicial do produto.
