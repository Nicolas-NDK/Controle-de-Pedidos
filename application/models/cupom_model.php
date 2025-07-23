<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cupom_model extends CI_Model 
{
    public function get_all() {
        $sql = "SELECT * FROM cupons ORDER BY id DESC";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function get_by_id($id) {
        $sql = "SELECT * FROM cupons WHERE id = ? LIMIT 1";
        $query = $this->db->query($sql, [$id]);
        return $query->row();
    }

    public function get_by_codigo($codigo) {
        $sql = "SELECT * FROM cupons WHERE codigo = ? AND ativo = 1 LIMIT 1";
        $query = $this->db->query($sql, [$codigo]);
        return $query->row();
    }

    public function inserir($data) 
    {
        $sql = "INSERT INTO cupons (codigo, desconto_percentual, desconto_valor, validade, minimo_subtotal, ativo) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $params = [
            $data['codigo'],
            $data['desconto_percentual'] ?? null,
            $data['desconto_valor'] ?? null,
            $data['validade'] ?? null,
            $data['minimo_subtotal'] ?? 0,
            $data['ativo'] ?? 1
        ];

        return $this->db->query($sql, $params);
    }

    public function deletar($id) {
        $sql = "DELETE FROM cupons WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function atualizar($id, $data) {
        $campos = [];
        $valores = [];

        foreach ($data as $coluna => $valor) {
            $campos[] = "$coluna = ?";
            $valores[] = $valor;
        }

        $valores[] = $id; // último parâmetro é o ID

        $sql = "UPDATE cupons SET " . implode(', ', $campos) . " WHERE id = ?";
        return $this->db->query($sql, $valores);
    }
}
