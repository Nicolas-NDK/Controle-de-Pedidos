<?php
class Carrinho extends CI_Controller {

        /** @var Produto_model */
    public $Produto_model;

    /** @var Cupom_model */
    public $Cupom_model;

    /** @var Cupom_model */
    public $Pedido_model;

    public function __construct() {
        parent::__construct();
        $this->load->model('Produto_model');
        $this->load->model('Cupom_model');
        $this->load->model('Pedido_model');
        $this->load->library('session');
        $this->load->library('upload');
        $this->load->helper(['url', 'form']);
        $this->load->library('email', $this->config->item('email'));

        require_once FCPATH . 'vendor/autoload.php';
    }

    public function ver() 
    {
        $carrinho = $this->session->userdata('carrinho');
        if (!is_array($carrinho)) {
            $carrinho = [];
        }

        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        if ($subtotal >= 52 && $subtotal <= 166.59) {
            $frete = 15.00;
        } elseif ($subtotal > 200) {
            $frete = 0.00;
        } else {
            $frete = 20.00;
        }

        $total = $subtotal + $frete;

        $cupom_sessao = $this->session->userdata('cupom_aplicado');
        $desconto = 0;

        if ($cupom_sessao) {
            $desconto = $cupom_sessao['desconto'] ?? 0;
        }

        $dados = [
            'carrinho' => $carrinho,
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total' => $total,
            'desconto' => $desconto,
        ];

        $data['cupons'] = $this->Cupom_model->get_all();

        $this->load->view('carrinho', $dados);
    }

    public function aplicar_cupom() 
    {
        $codigo = $this->input->post('codigo_cupom');
        $cupom = $this->Cupom_model->get_by_codigo($codigo);

        $carrinho = $this->session->userdata('carrinho');
        if (!is_array($carrinho) || empty($carrinho)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Carrinho vazio.']));
        }

        if (!$cupom) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Cupom inválido ou inativo.']));
        }

        // Verifica validade
       $hoje = date('Y-m-d');
        if (isset($cupom->validade) && $cupom->validade < $hoje) {
            // Desativa o cupom expirado
            $this->Cupom_model->atualizar($cupom->id, ['ativo' => 0]);

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Cupom expirado.']));
        }

        // Calcula subtotal do carrinho
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        // Verifica subtotal mínimo para o cupom
        if (isset($cupom->minimo_subtotal) && $subtotal < $cupom->minimo_subtotal) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => "Subtotal mínimo para este cupom é R$ " . number_format($cupom->minimo_subtotal, 2, ',', '.')
                ]));
        }

        // Calcula desconto
        $desconto = 0;
        if (!empty($cupom->desconto_percentual)) {
            $desconto = $subtotal * ($cupom->desconto_percentual / 100);
        } elseif (!empty($cupom->desconto_valor)) {
            $desconto = $cupom->desconto_valor;
        }

        // Salva cupom e desconto na sessão
        $this->session->set_userdata('cupom_aplicado', [
            'codigo' => $cupom->codigo,
            'desconto' => $desconto,
            'cupom_id' => $cupom->id
        ]);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => "Cupom aplicado! Desconto: R$ " . number_format($desconto, 2, ',', '.')
            ]));
    }

    //Limpar carrinho
    public function limpar() 
    {
        $this->session->unset_userdata('carrinho');
        redirect('carrinho/ver');
    }

    public function finalizar_pedido() 
    {
        $cep = $this->input->post('cep');
        $rua = $this->input->post('rua');
        $cidade = $this->input->post('cidade');
        $numero = $this->input->post('numero');
        $complemento = $this->input->post('complemento');
        $email = $this->input->post('email');
        $total_com_desconto = $this->input->post('total_com_desconto');

        if (!$cep || !$rua || !$cidade || !$numero || !$email || !$total_com_desconto) {
            $this->session->set_flashdata('erro', 'Preencha todos os campos obrigatórios.');
            return redirect('carrinho/ver');
        }

        $carrinho = $this->session->userdata('carrinho');
        if (empty($carrinho)) {
            $this->session->set_flashdata('erro', 'Carrinho vazio.');
            return redirect('carrinho/ver');
        }

        // Calcula frete
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        $frete = 20.0;
        if ($subtotal >= 52 && $subtotal <= 166.59) {
            $frete = 15.0;
        } elseif ($subtotal > 200) {
            $frete = 0.0;
        }

        // Dados do pedido
        $dados_pedido = [
            'total' => $total_com_desconto,
            'frete' => $frete,
            'cep' => $cep,
            'rua' => $rua,
            'cidade' => $cidade,
            'numero' => $numero,
            'complemento' => $complemento,
            'email' => $email,
            'status' => 'confirmado',
            'criado_em' => date('Y-m-d H:i:s')
        ];

        $itens_pedido = [];
        foreach ($carrinho as $item) {
            $itens_pedido[] = [
                'produto_id' => $item['produto_id'],
                'variacao_id' => isset($item['variacao_id']) ? $item['variacao_id'] : null,
                'quantidade' => $item['quantidade'],
                'preco_unitario' => $item['preco']
            ];
        }

        $pedido_id = $this->Pedido_model->inserir_pedido($dados_pedido, $itens_pedido);

        if (!$pedido_id) {
            $this->session->set_flashdata('erro', 'Erro ao salvar pedido.');
            return redirect('carrinho/ver');
        }

        // Enviar e-mail
        $this->email->clear();
        $this->email->from('email@gmail.com', 'Loja Exemplo');
        $this->email->to($email);
        $this->email->subject('Confirmação do Pedido #' . $pedido_id);

        $corpo = "Obrigado pelo seu pedido!\n\n";
        $corpo .= "Pedido Nº: $pedido_id\n";
        $corpo .= "Endereço:\n";
        $corpo .= "CEP: $cep\nRua: $rua\nCidade: $cidade\nNúmero: $numero\nComplemento: $complemento\n\n";
        $corpo .= "Total com desconto: R$ " . number_format($total_com_desconto, 2, ',', '.') . "\n";
        $corpo .= "Frete: R$ " . number_format($frete, 2, ',', '.') . "\n\n";
        $corpo .= "Agradecemos pela compra!";

        $this->email->message($corpo);

        if (!$this->email->send()) {
            $this->session->unset_userdata('carrinho');
            $this->session->set_flashdata('erro', 'Pedido salvo, mas falha ao enviar o e-mail.');
            return redirect('carrinho/sucesso');
        }

        $this->session->unset_userdata('carrinho');
        $this->session->set_flashdata('sucesso', 'Pedido finalizado com sucesso! E-mail enviado.');

        return redirect('carrinho/sucesso');
    }

    public function sucesso() 
    {
        $this->load->view('sucesso');
    }

    public function remover_carrinho($produto_id)
    {
        $carrinho = $this->session->userdata('carrinho') ?? [];

        if (isset($carrinho[$produto_id])) {
            $carrinho[$produto_id]['quantidade']--;

            if ($carrinho[$produto_id]['quantidade'] <= 0) {
                unset($carrinho[$produto_id]);
            }

            $this->session->set_userdata('carrinho', $carrinho);
        }

        redirect('carrinho/ver');
    }
}