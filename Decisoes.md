# DECISOES.md

## Modelagem do banco de dados

A modelagem foi uma das primeiras coisas que pensei antes de escrever qualquer linha de código. Precisava garantir que as relações entre entidades fossem claras e que o banco não virasse um problema lá na frente.

### Tabelas criadas

**`fornecedores`**
Tabela direta, sem muita complexidade. Adicionei `soft deletes` aqui porque fornecedores podem ser inativados mas não devem ser apagados do histórico — se um pedido antigo referencia aquele fornecedor, preciso que o registro ainda exista. Indexei `nome` e `status` por serem os campos mais usados em filtros.

**`produtos`**
Mesma lógica do fornecedor com soft delete. O `codigo_interno` tem índice único porque é um identificador de negócio e não pode se repetir.

**`produto_fornecedor`**
Tabela pivot para o relacionamento N:N entre produtos e fornecedores. Coloquei um índice composto único em `(produto_id, fornecedor_id)` para evitar vínculos duplicados no banco, independente de qualquer validação na aplicação.

**`pedidos`**
Decidi desnormalizar o `valor_total` no pedido para não precisar fazer um `SUM` na query toda vez que listo pedidos. Ele é recalculado automaticamente via `boot()` no model `ItemPedido` sempre que um item é salvo ou removido. Pode parecer redundância, mas na prática é um ganho real de performance em listagens.

**`itens_pedido`**
Cada linha de produto dentro de um pedido. O `valor_total` do item também é calculado automaticamente no model antes de salvar (`quantidade × valor_unitario`).

**`historico_status_pedido`**
Decidi criar uma tabela separada para isso em vez de guardar só o status atual. Dessa forma consigo rastrear toda a evolução do pedido ao longo do tempo, o que é muito mais útil para o time comercial do que só saber o estado atual.

---

## Decisões de arquitetura

### Por que Laravel com Vue.js como SPA?

O Laravel já era obrigatório pelo escopo do teste. Para o frontend, optei por montar uma SPA com Vue 3 dentro do próprio projeto Laravel usando Vite, em vez de separar em dois repositórios. Para o escopo de um sistema interno, isso é mais simples de manter e fazer deploy.

### Por que não usei o `routes/api.php`?

No Laravel 12, o `routes/api.php` aplica automaticamente o prefixo `/api` e middlewares de API. Como eu já estava gerenciando isso manualmente com `Route::prefix('api')` no `web.php`, preferi manter tudo em um arquivo só para simplificar. Menos arquivos para se perocupar, menos chance de conflito.

### Scopes nos Models

Em vez de repetir `->where('status', 'ativo')` em vários controllers, criei scopes como `scopeAtivo()`, `scopeSearch()` e `scopePorStatus()` nos models. É uma prática simples que torna as queries muito mais legíveis e evita retrabalho se a lógica de filtro mudar.

### boot() no ItemPedido

Coloquei o cálculo de `valor_total` do item e o recálculo do total do pedido dentro do `boot()` do model `ItemPedido`. Dessa forma, independente de onde o item for criado ou deletado — seja no controller, num job, num seeder — o total sempre vai estar correto. Não preciso lembrar de chamar o cálculo em todo lugar.

---

## Desafios

### Opção A — Experiência do usuário

Escolhi implementar a Opção A porque acredito que um sistema interno só é adotado de verdade pelo time se for agradável de usar. De nada adianta ter todas as funcionalidades se o usuário precisa de três cliques para fazer algo simples.

### Opção B — Regras de negócio

A Opção B foi quase obrigatória do ponto de vista de integridade do sistema. Um sistema comercial sem regras de negócio bem definidas vira uma fonte de dados inconsistentes.

As regras que implementei:
- **Fornecedor inativo não aceita novo pedido** — verificação no controller antes de criar, retornando erro 422 com mensagem clara
- **Pedidos concluídos e cancelados são somente leitura** — o método `isBloqueado()` no model centraliza essa verificação, usada tanto no backend (bloqueia update/adição de itens) quanto no frontend (desabilita botões e exibe banner de aviso)
- **Produto só entra no pedido se estiver ativo e vinculado ao fornecedor** — regra validada no controller de itens antes de inserir
- **Histórico de status automático** — toda mudança de status gera um registro com o estado anterior, o novo estado e o timestamp

Essas regras não foram difíceis de implementar, mas são o tipo de coisa que faz diferença no dia a dia do time comercial.

---

## Uso de I.A no desenvolvimento

Além do apoio no frontend mencionado acima, também utilizei inteligência artificial para acelerar a criação dos modelos de banco de dados e das migrations. Usar IA nessa etapa me ajudou a estruturar as tabelas mais rápido, pensar nos índices certos e garantir que os relacionamentos estivessem corretos desde o início — coisas que normalmente consomem bastante tempo quando feitas do zero.

Vale deixar claro que o uso foi como uma ferramenta de apoio: eu revisava tudo que era gerado, ajustava o que não fazia sentido para o contexto do projeto e tomava as decisões finais. A IA acelerou o processo, mas o entendimento e a responsabilidade sobre cada decisão foi minha.

---

## O que melhoraria com mais tempo

- **Camada de Services** — extrair a lógica de negócio dos controllers para classes de serviço dedicadas (`PedidoService`, `VinculoService`). Os controllers estão funcionais mas ficaram um pouco gordos
- **Testes automatizados** — escrever Feature Tests para os endpoints principais usando Pest ou PHPUnit
- **Laravel Horizon** — para monitorar as filas com uma interface visual, em vez de depender só dos logs
- **Máscara de campos** — aplicar formatação automática de CNPJ e telefone no frontend
- **Exportação de pedidos** — gerar PDF ou Excel dos pedidos para o time comercial