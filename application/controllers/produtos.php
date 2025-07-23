<?php
class Produtos extends CI_Controller {

        /** @var Produto_model */
    public $Produto_model;

    public function __construct() {
        parent::__construct();
        $this->load->model('Produto_model');
        $this->load->library('session');
        $this->load->library('upload');
        $this->load->helper(['url', 'form']);

        require_once FCPATH . 'vendor/autoload.php';
    }

    public function index() 
    {
        redirect('produtos/salvar');
    }

    public function salvar()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->model('Produto_model');

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger">', '</div>');
        $this->form_validation->set_rules('nome', 'Nome', 'required|min_length[2]');
        $this->form_validation->set_rules('preco', 'Preço', 'required|numeric');
        $this->form_validation->set_rules('estoque_simples', 'Estoque simples ou variações', 'callback_validar_exclusividade_estoque_variacoes');


        // Captura flashdata da mensagem para exibir na view
        $dados['mensagem_sucesso'] = $this->session->flashdata('mensagem_sucesso');
        $dados['erro_duplicado']   = $this->session->flashdata('erro_duplicado');

        if ($this->input->post()) {
            if ($this->form_validation->run() === TRUE) {
                $produto_id      = $this->input->post('id');
                $nome            = $this->input->post('nome');
                $preco           = $this->input->post('preco');
                $variacoes       = $this->input->post('variacoes');
                $estoque_simples = $this->input->post('estoque_simples');

                // Verifica duplicado
                if ($this->Produto_model->produto_ja_existe($nome, $preco, $variacoes, $estoque_simples, $produto_id)) {
                    $this->session->set_flashdata('erro_duplicado', 'Já existe um produto idêntico cadastrado.');
                    redirect('produtos/salvar');
                    return;
                }

                if (!empty($produto_id)) {
                    // Atualização
                    $this->Produto_model->atualizar_produto($produto_id, $nome, $preco);
                    $this->Produto_model->deletar_estoque_simples($produto_id);
                    $this->Produto_model->deletar_variacoes_por_produto($produto_id);

                    if (!empty($estoque_simples)) {
                        $this->Produto_model->inserir_estoque_simples($produto_id, $estoque_simples);
                    } elseif (!empty($variacoes)) {
                        foreach ($variacoes as $v) {
                            if (!empty($v['nome'])) {
                                $this->Produto_model->inserir_variacao(
                                    $produto_id,
                                    $v['nome'],
                                    $v['preco_extra'] ?? 0,
                                    $v['estoque'] ?? 0
                                );
                            }
                        }
                    }

                    $this->session->set_flashdata('mensagem_sucesso', 'Produto editado com sucesso!');
                } else {
                    // Criação
                    $produto_id = $this->Produto_model->inserir_produto($nome, $preco);

                    if (!empty($estoque_simples)) {
                        $this->Produto_model->inserir_estoque_simples($produto_id, $estoque_simples);
                    } elseif (!empty($variacoes)) {
                        foreach ($variacoes as $v) {
                            if (!empty($v['nome'])) {
                                $this->Produto_model->inserir_variacao(
                                    $produto_id,
                                    $v['nome'],
                                    $v['preco_extra'] ?? 0,
                                    $v['estoque'] ?? 0
                                );
                            }
                        }
                    }

                    $this->session->set_flashdata('mensagem_sucesso', 'Produto cadastrado com sucesso!');
                }

                redirect('produtos/salvar');
                return;
            } else {
                $dados['erro_validacao'] = true;
            }
        }

        $dados['produtos'] = $this->Produto_model->get_all();
        $total_itens = 0;
        $carrinho = $this->session->userdata('carrinho');

        if (is_array($carrinho)) {
            foreach ($carrinho as $item) {
                $total_itens += $item['quantidade'];
            }
        } else {
            $carrinho = [];
        }
        $dados['total_itens_carrinho'] = $total_itens;
        $this->load->view('cadastrar_produtos', $dados);
    }

    public function listar_produtos()
    {
        $this->load->model('Produto_model');
        $produtos = $this->Produto_model->get_all();

        $carrinho = $this->session->userdata('carrinho') ?? [];

        // Soma total de itens no carrinho
        $total_itens = 0;
        foreach ($carrinho as $item) {
            $total_itens += $item['quantidade'];
        }

        $dados = [
            'produtos' => $produtos,
            'total_itens_carrinho' => $total_itens
        ];

        $this->load->view('cadastrar_produtos', $dados);
    }

    public function adicionar_carrinho()
    {
        $produto_id = $this->input->post('produto_id');
        $this->load->model('Produto_model');

        $produto = $this->Produto_model->get_by_id($produto_id);
        ob_clean();

        if (!$produto) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Produto não encontrado']));
            exit;
        }

        $carrinho = $this->session->userdata('carrinho');
        if (!is_array($carrinho)) {
            $carrinho = [];
        }

        // Verifica se produto já está no carrinho e valida estoque
        $quantidade_no_carrinho = isset($carrinho[$produto_id]['quantidade']) ? $carrinho[$produto_id]['quantidade'] : 0;

        if ($produto->quantidade && $quantidade_no_carrinho >= $produto->quantidade->quantidade) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Estoque insuficiente.']));
            exit;
        }

        if (isset($carrinho[$produto_id])) {
            $carrinho[$produto_id]['quantidade'] += 1;
        } else {
            $carrinho[$produto_id] = [
                'produto_id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => $produto->preco,
                'quantidade' => 1
            ];
        }

        // Salva novamente na sessão
        $this->session->set_userdata('carrinho', $carrinho);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
        exit;
    }

    public function excluir()
    {
        $id = $this->input->post('id');
        if (!$id) {
            $this->session->set_flashdata('erro', 'ID inválido.');
            redirect('produtos/salvar');
            return;
        }

        $this->load->model('Produto_model');

        $deletado = $this->Produto_model->excluir_produto($id);

        if ($deletado) {
            $this->session->set_flashdata('mensagem_sucesso', 'Produto excluído com sucesso!');
        } else {
            $this->session->set_flashdata('erro', 'Erro ao excluir o produto.');
        }

        redirect('produtos/salvar');
    }

    #################### AJAX #############################

    public function get_json($id)
    {
        $this->load->model('Produto_model');

        $produto = $this->Produto_model->get_by_id($id);

        if ($produto) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'produto' => $produto]));
        } else {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'error' => 'Produto não encontrado']));
        }
    }

    #################### CALLBACK  #############################


    public function validar_exclusividade_estoque_variacoes($estoque_simples)
    {
        $variacoes = $this->input->post('variacoes');

        $tem_estoque_simples = !empty($estoque_simples);
        $tem_variacoes_validas = false;

        if (!empty($variacoes) && is_array($variacoes)) {
            foreach ($variacoes as $v) {
                if (!empty($v['nome'])) {
                    $tem_variacoes_validas = true;
                    break;
                }
            }
        }

        if (($tem_estoque_simples && $tem_variacoes_validas) || (!$tem_estoque_simples && !$tem_variacoes_validas)) {
            $this->form_validation->set_message('validar_exclusividade_estoque_variacoes', 'Você deve preencher apenas o estoque simples ou as variações, nunca os dois ou nenhum.');
            return false;
        }

        return true;
    }


}