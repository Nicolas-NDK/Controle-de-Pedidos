# Sistema de Pedidos com Cupons e Estoque

Este é um sistema simples de gerenciamento de produtos, pedidos, cupons e controle de estoque. Desenvolvido com CodeIgniter 3 e MySQL, o projeto contempla os seguintes recursos:

---

## 🔧 Funcionalidades

- ✅ Cadastro de produtos com suporte a variações
- ✅ Controle de estoque (produto simples ou com variações)
- ✅ Carrinho com regras de frete
- ✅ Aplicação de cupons com:
  - Desconto percentual ou valor fixo
  - Validade e valor mínimo de subtotal
- ✅ Consulta automática de endereço por CEP (ViaCEP)
- ✅ Finalização de pedido com envio de e-mail
- ✅ Webhook para atualizar ou excluir pedidos com base no status

---

## 🚀 Tecnologias Utilizadas

- PHP 7+
- CodeIgniter 3
- MySQL
- jQuery
- Bootstrap 5
- API ViaCEP (consulta de endereço)
- PHPMailer (envio de e-mail)

---

## Estrutura do Banco de Dados

- **produtos**: nome, preço
- **variacoes**: produto_id, nome, preco_extra
- **estoque**: produto_id, variacao_id, quantidade
- **cupons**: código, tipo de desconto, validade, subtotal mínimo, ativo
- **pedidos**: dados do cliente, status, valor total
- **pedido_itens**: pedido_id, produto_id, variacao_id, quantidade, preço unitário

---

## Regras de Frete

| Subtotal                  | Frete     |
|---------------------------|-----------|
| Até R$52,00               | R$20,00   |
| De R$52,01 até R$166,59   | R$15,00   |
| Acima de R$200,00         | Grátis    |

---

## Testando o Webhook com Insomnia

Utilizei o Insomnia para simular requisições POST para o endpoint responsável. Seguem alguns exemplos de testes realizados:

### 1. Para pedido entregue

http://localhost/montink/webhook/atualizar_status

```json
{
  "pedido_id": 1,
  "status": "entregue"
}

#### Resposta esperada

{
	"success": true,
	"message": "Status do pedido atualizado"
}



### 2. Para cancelar um pedido

http://localhost/montink/webhook/atualizar_status

```json
{
  "pedido_id": 1,
  "status": "cancelado"
}

#### Resposta esperada

{
	"success": true,
	"message": "Pedido cancelado e removido"
}


### Banco de Dados

O dump se encontra na raiz do projeto com o nome de montink_schema.sql 