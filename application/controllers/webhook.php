<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Pedido_model');
        $this->load->helper('url');
    }

    public function atualizar_status() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['pedido_id']) || !isset($input['status'])) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Dados incompletos'
                ]));
        }

        $pedido_id = $input['pedido_id'];
        $status = $input['status'];

        // Verifica se o pedido existe
        $pedido = $this->Pedido_model->get_by_id($pedido_id);
        if (!$pedido) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Pedido não encontrado'
                ]));
        }

        if (strtolower($status) === 'cancelado') {
            $this->Pedido_model->excluir_pedido($pedido_id);
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Pedido cancelado e removido'
                ]));
        } else {
            $this->Pedido_model->atualizar_status($pedido_id, $status);
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Status do pedido atualizado'
                ]));
        }
    }
}
