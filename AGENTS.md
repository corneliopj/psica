# AGENTS.md — Protocolo de Trabalho dos Agentes de IA

## 1. Propósito

Este arquivo define as regras que agentes de Inteligência Artificial devem seguir ao trabalhar neste projeto.

O projeto pode ser desenvolvido por diferentes modelos, em diferentes sessões e em diferentes momentos. Portanto, nenhum agente deve depender exclusivamente da memória da conversa atual.

O repositório é a memória persistente do projeto.

O objetivo de cada agente é:

1. compreender corretamente o estado atual do projeto;
2. preservar decisões anteriores;
3. realizar a tarefa solicitada;
4. verificar suas alterações;
5. atualizar o contexto persistente quando necessário;
6. deixar o projeto em condições de ser continuado por outro agente.

---

# 2. Hierarquia das fontes de contexto

Antes de tomar decisões, considere as fontes na seguinte ordem:

1. `AGENTS.md`
2. `README.md`
3. `docs/Project Context.md`
4. `docs/Architectural and Project Decisions.md`
5. `docs/Architectural and Project Decisions.md`
6. `docs/Current State.md`
7. `docs/Work Log.md`
8. código-fonte
9. testes
10. configurações e demais arquivos
11. contexto fornecido pela conversa atual

A conversa atual pode fornecer instruções específicas para a tarefa, mas não deve apagar ou ignorar decisões persistentes do projeto sem justificativa.

Em caso de conflito entre documentos, o agente deve identificar o conflito antes de decidir.

---

# 3. Regra fundamental

## NÃO MODIFIQUE ANTES DE ENTENDER.

Antes de alterar código, determine:

- o que o projeto faz;
- qual problema está sendo resolvido;
- como a arquitetura funciona;
- quais componentes estão envolvidos;
- quais decisões já foram tomadas;
- qual é o estado atual;
- quais limitações são conhecidas;
- quais testes existem;
- quais consequências a alteração poderá produzir.

Não substitua uma decisão existente simplesmente porque existe outra solução tecnicamente possível.

---

# 4. Contexto persistente versus contexto temporário

### Contexto persistente

Informações que precisam sobreviver à troca de agente devem estar no repositório.

Exemplos:

- decisões arquiteturais;
- requisitos;
- restrições;
- convenções;
- problemas conhecidos;
- estado atual;
- razões para decisões importantes.

### Contexto temporário

Informações existentes somente na conversa atual.

Uma informação importante não deve permanecer apenas no contexto temporário.

Se uma decisão relevante surgir durante uma tarefa, ela deve ser registrada no documento apropriado.

---

# 5. Protocolo de início

Ao iniciar uma tarefa relevante:

1. leia este arquivo;
2. leia `README.md`;
3. leia `docs/Project Context.md`;
4. leia `docs/Current State.md`;
5. consulte `docs/Architectural and Project Decisions.md` quando a tarefa envolver arquitetura;
6. consulte `docs/Architectural and Project Decisions.md` quando houver decisões relacionadas;
7. consulte `docs/Work Log.md` quando o histórico recente for relevante;
8. examine o código e os testes necessários.

Não leia indiscriminadamente todo o repositório se isso não for necessário.

Faça uma análise orientada pela tarefa.

---

# 6. Classificação das informações

Sempre que possível, diferencie:

### FATO

Informação comprovada pelo código, documentação ou configuração.

### INFERÊNCIA

Conclusão derivada da análise do projeto.

### HIPÓTESE

Possibilidade ainda não confirmada.

### PROPOSTA

Nova decisão sugerida pelo agente.

Nunca apresente uma hipótese como se fosse um fato.

---

# 7. Protocolo de execução

Para tarefas complexas, siga:

## Etapa 1 — Entendimento

Descreva o que foi solicitado.

## Etapa 2 — Investigação

Localize os componentes relevantes.

## Etapa 3 — Diagnóstico

Determine como o sistema funciona atualmente e qual é o problema.

## Etapa 4 — Alternativas

Quando houver decisões relevantes, avalie alternativas.

## Etapa 5 — Decisão

Escolha a solução mais coerente com o projeto.

## Etapa 6 — Implementação

Faça somente as alterações necessárias.

## Etapa 7 — Validação

Execute testes, lint, build ou outras verificações disponíveis.

## Etapa 8 — Persistência

Atualize a documentação quando a tarefa produzir conhecimento relevante para agentes futuros.

---

# 8. Princípio da menor alteração

Prefira:

- alterações pequenas;
- alterações localizadas;
- preservação de APIs existentes;
- compatibilidade;
- reutilização de componentes;
- soluções coerentes com a arquitetura atual.

Evite refatorações abrangentes quando elas não forem necessárias para resolver a tarefa.

---

# 9. Código existente

Nunca presuma que código aparentemente estranho está errado.

Antes de removê-lo ou substituí-lo, procure entender:

- dependências;
- compatibilidade;
- decisões anteriores;
- testes;
- requisitos;
- limitações técnicas.

Se uma alteração puder quebrar comportamento existente, identifique o risco.

---

# 10. Testes

Sempre que possível:

- execute testes relacionados à alteração;
- execute lint;
- execute build;
- valide comportamento relevante.

Se não puder executar uma verificação, registre isso.

Nunca declare que algo foi testado se não foi.

---

# 11. Documentação

Atualize a documentação quando ocorrer:

- nova decisão arquitetural;
- mudança significativa de comportamento;
- alteração de API;
- mudança de banco de dados;
- nova dependência relevante;
- alteração de requisito;
- descoberta importante;
- mudança de estado relevante.

Não altere documentação apenas para gerar atividade.

---

# 12. Decisões arquiteturais

Decisões importantes devem ser registradas em:

`docs/Architectural and Project Decisions.md`

Uma decisão deve registrar:

- contexto;
- problema;
- alternativas;
- decisão;
- justificativa;
- consequências;
- status.

---

# 13. Estado atual

Ao concluir uma tarefa significativa, avalie se:

`docs/Current State.md`

precisa ser atualizado.

Esse arquivo deve representar o estado atual real do projeto.

Não deixe informações obsoletas.

---

# 14. Histórico de trabalho

Use:

`docs/Work Log.md`

para registrar acontecimentos relevantes.

O log deve ser conciso.

Não transforme o arquivo em uma transcrição da conversa.

---

# 15. Conflitos

Se houver conflito entre:

- documentação e código;
- duas decisões;
- requisitos;
- testes e comportamento esperado;

não resolva silenciosamente.

Informe:

1. qual é o conflito;
2. quais fontes sustentam cada interpretação;
3. qual interpretação parece correta;
4. qual alteração é recomendada.

---

# 16. Informações ausentes

Se faltar informação:

1. procure no repositório;
2. procure nos documentos;
3. procure nos testes;
4. examine histórico disponível;
5. somente então pergunte ao usuário.

Nunca invente requisitos.

---

# 17. Handoff

Ao finalizar uma tarefa significativa, produza um resumo que permita a outro agente continuar.

O resumo deve conter:

- o que foi feito;
- arquivos alterados;
- testes executados;
- decisões tomadas;
- problemas encontrados;
- pendências;
- próximo passo recomendado.

---

# 18. Princípio de continuidade

O agente não está apenas resolvendo uma tarefa.

Ele está participando de um projeto contínuo.

Portanto:

> Toda alteração importante deve deixar o projeto mais compreensível para o próximo agente do que estava antes.

---

# 19. Regra final

Antes de finalizar uma tarefa, pergunte internamente:

> "Se outro modelo assumir este projeto amanhã, ele conseguirá entender o que fiz, por que fiz e o que precisa fazer depois?"

Se a resposta for não, melhore a documentação ou o handoff.