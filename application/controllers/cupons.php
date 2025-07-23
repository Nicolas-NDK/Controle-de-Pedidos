<?php
class Cupons extends CI_Controller {

        /** @var Cupom_model */
    public $Cupom_model;

    public function __construct() {
        parent::__construct();
        $this->load->model('Cupom_model');
        $this->load->library('session');
        $this->load->library('upload');
        $this->load->helper(['url', 'form']);

        require_once FCPATH . 'vendor/autoload.php';
    }

    public function index() 
    {
        $data['cupons'] = $this->Cupom_model->get_all();
        $this->load->view('listar_cupons', $data);
    }

    public function criar() 
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('codigo', 'Código', 'required|is_unique[cupons.codigo]');
        $this->form_validation->set_rules('validade', 'Validade', 'required');
        $this->form_validation->set_rules('minimo_subtotal', 'Subtotal Mínimo', 'required|numeric');

        $percentual = $this->input->post('desconto_percentual');
        $valor_fixo = $this->input->post('desconto_valor');

        if (empty($percentual) && empty($valor_fixo)) {
            $this->form_validation->set_rules('dummy', '', 'required', [
                'required' => 'Você deve informar ao menos um tipo de desconto: percentual ou valor fixo.'
            ]);
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('criar_cupons');
        } else {
            $data = [
                'codigo' => $this->input->post('codigo'),
                'desconto_percentual' => $percentual ?: null,
                'desconto_valor' => $valor_fixo ?: null,
                'validade' => $this->input->post('validade'),
                'minimo_subtotal' => $this->input->post('minimo_subtotal'),
                'ativo' => 1
            ];

            $this->Cupom_model->inserir($data);
            $this->session->set_flashdata('success', 'Cupom criado com sucesso.');
            redirect('cupons');
        }
    }

    public function toggle_ativo($id) 
    {
        $cupom = $this->Cupom_model->get_by_id($id);

        if (!$cupom) {
            $this->session->set_flashdata('error', 'Cupom não encontrado.');
            redirect('cupons');
            return;
        }

        $novo_status = $cupom->ativo ? 0 : 1;

        $this->Cupom_model->atualizar($id, ['ativo' => $novo_status]);

        $msg = $novo_status ? 'Cupom ativado com sucesso.' : 'Cupom desativado com sucesso.';
        $this->session->set_flashdata('success', $msg);
        redirect('cupons');
    }

    public function deletar($id) 
    {
        $this->Cupom_model->deletar($id);
        $this->session->set_flashdata('success', 'Cupom deletado com sucesso.');
        redirect('cupons');
    }
}
