<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Produto_model extends CI_Model 
{
    public function get_all() {
        $sql = "SELECT * FROM produtos ORDER BY id DESC";
        return $this->db->query($sql)->result();
    }

    public function get_variacoes_por_produto($produto_id) {
        $sql = "SELECT * FROM variacoes WHERE produto_id = ?";
        return $this->db->query($sql, [$produto_id])->result();
    }

    public function get_estoque_por_produto($produto_id) {
        $sql = "SELECT * FROM estoque WHERE produto_id = ? AND variacao_id IS NULL";
        return $this->db->query($sql, [$produto_id])->row();
    }

    public function get_estoque_por_variacao($variacao_id) {
        $sql = "SELECT * FROM estoque WHERE variacao_id = ?";
        return $this->db->query($sql, [$variacao_id])->row();
    }

    public function inserir_produto($nome, $preco) {
        $sql = "INSERT INTO produtos (nome, preco) VALUES (?, ?)";
        $this->db->query($sql, [$nome, $preco]);
        return $this->db->insert_id();
    }

    public function inserir_variacao($produto_id, $nome, $preco_extra, $estoque) {
        $sql1 = "INSERT INTO variacoes (produto_id, nome, preco_extra) VALUES (?, ?, ?)";
        $this->db->query($sql1, [$produto_id, $nome, $preco_extra]);
        $variacao_id = $this->db->insert_id();

        $sql2 = "INSERT INTO estoque (produto_id, variacao_id, quantidade) VALUES (?, ?, ?)";
        $this->db->query($sql2, [$produto_id, $variacao_id, $estoque]);
    }

    public function inserir_estoque_simples($produto_id, $estoque_simples) {
        $sql = "INSERT INTO estoque (produto_id, quantidade) VALUES (?, ?)";
        $this->db->query($sql, [$produto_id, $estoque_simples]);
    }

    public function get_by_id($id) {
        $sql = "SELECT * FROM produtos WHERE id = ?";
        $produto = $this->db->query($sql, [$id])->row();

        if ($produto) {
            $variacoes = $this->get_variacoes_por_produto($id);
            foreach ($variacoes as &$v) {
                $v->estoque = $this->get_estoque_por_variacao($v->id) ?? null;

                if ($v->estoque) {
                    if (empty($v->estoque->variacao_id)) unset($v->estoque->variacao_id);
                    if (empty($v->estoque->quantidade) && $v->estoque->quantidade !== 0) unset($v->estoque->quantidade);
                }
            }
            $produto->variacoes = $variacoes;

            $produto->quantidade = $this->get_estoque_por_produto($id) ?? null;

            if ($produto->quantidade) {
                if (empty($produto->quantidade->variacao_id)) unset($produto->quantidade->variacao_id);
                if (empty($produto->quantidade->quantidade) && $produto->quantidade->quantidade !== 0) unset($produto->quantidade->quantidade);
            }
        }

        return $produto;
    }

    public function atualizar_produto($id, $nome, $preco) {
        $sql = "UPDATE produtos SET nome = ?, preco = ? WHERE id = ?";
        return $this->db->query($sql, [$nome, $preco, $id]);
    }

    public function deletar_variacoes_por_produto($produto_id) {
        $sql = "DELETE FROM variacoes WHERE produto_id = ?";
        return $this->db->query($sql, [$produto_id]);
    }

    public function deletar_estoque_simples($produto_id) {
        $sql = "DELETE FROM estoque WHERE produto_id = ?";
        return $this->db->query($sql, [$produto_id]);
    }

    public function produto_ja_existe($nome, $preco, $variacoes = [], $estoque_simples = null, $ignorar_id = null) {
        $params = [$nome, $preco];
        $sql = "SELECT * FROM produtos WHERE nome = ? AND preco = ?";
        if ($ignorar_id) {
            $sql .= " AND id != ?";
            $params[] = $ignorar_id;
        }

        $produtos = $this->db->query($sql, $params)->result();

        foreach ($produtos as $produto) {
            $sql_var = "SELECT * FROM variacoes WHERE produto_id = ?";
            $db_variacoes = $this->db->query($sql_var, [$produto->id])->result_array();
            usort($db_variacoes, fn($a, $b) => strcmp($a['nome'], $b['nome']));

            $input_variacoes = $variacoes ?? [];
            usort($input_variacoes, fn($a, $b) => strcmp($a['nome'], $b['nome']));

            $sql_estoque = "SELECT * FROM estoque WHERE produto_id = ?";
            $estoque = $this->db->query($sql_estoque, [$produto->id])->row();

            $variacoes_iguais = json_encode($db_variacoes) === json_encode($input_variacoes);
            $estoque_igual = ($estoque && $estoque_simples)
                ? ((int)$estoque->quantidade === (int)$estoque_simples)
                : (!$estoque && !$estoque_simples);

            if ($variacoes_iguais && $estoque_igual) {
                return true;
            }
        }

        return false;
    }

    public function excluir_produto($id) {
        $this->deletar_variacoes_por_produto($id);
        $this->deletar_estoque_simples($id);

        $sql = "DELETE FROM produtos WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}
