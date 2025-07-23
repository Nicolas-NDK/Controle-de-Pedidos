<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pedido_model extends CI_Model {

    public function inserir_pedido($dados_pedido, $itens) {
        // Insere o pedido
        $sqlPedido = "INSERT INTO pedidos (cep, rua, cidade, numero, complemento, email, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $pedidoExecutado = $this->db->query($sqlPedido, [
            $dados_pedido['cep'],
            $dados_pedido['rua'],
            $dados_pedido['cidade'],
            $dados_pedido['numero'],
            $dados_pedido['complemento'],
            $dados_pedido['email'],
            $dados_pedido['total'],
            isset($dados_pedido['status']) ? $dados_pedido['status'] : 'pendente'
        ]);

        if (!$pedidoExecutado) {
            return false;
        }

        $pedido_id = $this->db->insert_id();
         // Insere o os itens do pedido
        $sqlItem = "INSERT INTO pedido_itens (pedido_id, produto_id, variacao_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)";

        foreach ($itens as $item) {
            $ok = $this->db->query($sqlItem, [
                $pedido_id,
                $item['produto_id'],
                isset($item['variacao_id']) ? $item['variacao_id'] : null,
                $item['quantidade'],
                $item['preco_unitario']
            ]);

            if (!$ok) {
                return false;
            }
        }

        return $pedido_id;
    }


    public function atualizar_status($pedido_id, $status) {
        $sql = "UPDATE pedidos SET status = ? WHERE id = ?";
        return $this->db->query($sql, [$status, $pedido_id]);
    }

    public function excluir_pedido($pedido_id) {
        $this->db->query("DELETE FROM pedido_itens WHERE pedido_id = ?", [$pedido_id]);
        return $this->db->query("DELETE FROM pedidos WHERE id = ?", [$pedido_id]);
    }

    public function get_by_id($id) {
        $sql = "SELECT * FROM pedidos WHERE id = ?";
        return $this->db->query($sql, [$id])->row();
    }

}
