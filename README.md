# Sistema Web de Gestão de Produtos e Fornecedores


> **Nota importante:** É **obrigatório** ter o **Docker** e o **Docker Compose** instalados e em execução para rodar este projeto localmente.

## Pré-requisitos

* Docker (instalado e em execução)
* Docker Compose (instalado)
* Git
* Node v20 +
* Npm v10 +

## Passo a passo

1. Clone Repositório
```
git clone git@github.com:rafPH1998/teste-virtual-market.git
cd teste-virtual-market
```

2. Crie o Arquivo .env

```
cp .env.example .env
```

3. Suba os containers do projeto

```
docker compose up -d
```

4. Entre dentro do container

```
 docker compose exec app bash
```

5. Dentro do container, rode o comando abaixo para gerar as dependencias do projeto

```
composer install
```

6. Gere a key do projeto ainda dentro do container

```
php artisan key:generate
```

# Caso gere erro ao rodar composer install, é importante saber o id que se encontra o seu usuário

![alt text](image-2.png)

Verifique se o ID esta igual ao do arquivo Dockerfile, caso não estiver, deixe o ID do dockerfile similar ao do usuario. Rode docker compose up --build para subir os container de novo e tente o processo novamente.

![alt text](image-1.png)

## Fora do container, rode para baixar as dependencias do frontend:

```
npm install
```

## Rode o comando abaixo para visualizar o painel e acessar as funcionalidades do sistema

```
npm run dev
```

## Acesse o sistema

```
http://localhost:8007/fornecedores
```

## Deixe a fila rodando sempre. Rode:

```
docker compose exec app bash
```

## Em seguida
```
php artisan queue:work
```

---------------------------------------------------------------------------------------------------------------------

## Funcionalidades


### Fornecedores
- Listagem com busca em tempo real por nome, CNPJ ou e-mail
- Filtro por status (Ativo / Inativo)
- Cadastro e edição via modal com validação de campos
- Exclusão com verificação de pedidos ativos vinculados

### Produtos
- Listagem com busca por nome ou código interno
- Filtro por status (Ativo / Inativo)
- Cadastro e edição via modal com validação de campos
- Acesso direto ao gerenciamento de vínculos por produto

### Vínculos Produto ↔ Fornecedor
- Interface dividida em dois painéis: fornecedores vinculados e disponíveis
- Vínculo e desvínculo individual com feedback imediato
- Vínculo em massa via seleção múltipla (processado em fila Redis)
- Desvínculo em massa via seleção múltipla (processado em fila Redis)

### Pedidos
- Listagem com filtro por status e por fornecedor
- Cards de resumo clicáveis com contagem por status (Aberto, Processando, Concluído, Cancelado)
- Cadastro e edição de pedido com validação
- Bloqueio de edição para pedidos concluídos ou cancelados
- Impedimento de criação de pedido para fornecedor inativo

### Itens do Pedido
- Adição de itens com seleção de produto, quantidade e valor unitário
- Somente produtos ativos e vinculados ao fornecedor do pedido são listados
- Cálculo automático do valor total do item e do pedido
- Remoção de itens com recálculo automático do total

### Histórico de Status
- Registro automático de toda mudança de status do pedido
- Exibido na tela de detalhe com data, hora e estados anterior e novo