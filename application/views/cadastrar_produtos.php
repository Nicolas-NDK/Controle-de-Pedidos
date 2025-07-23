<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body>

<style>
    .produto-card {
        transition: transform 0.2s;
        text-align: center;
    }

    .produto-card:hover {
        transform: scale(1.02);
    }

    .produto-imagem-simulada {
        background-color: #f1f1f1;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        border-radius: 6px;
    }

    .produto-imagem-simulada i {
        font-size: 40px;
        color: #777;
    }

    .produto-card .btn {
        margin-top: 10px;
    }
</style>


<?php include('header.php'); ?>

<div class="container py-5">
    <h2 class="mb-4">Cadastrar Produto</h2>

    <?php if (!empty($mensagem_sucesso)): ?>
        <div id="alerta-sucesso" class="alert alert-success"><?= $mensagem_sucesso ?></div>
    <?php endif; ?>

    <?php if (!empty($erro_duplicado)): ?>
        <div id="alerta-erro-duplicado" class="alert alert-danger"><?= $erro_duplicado ?></div>
    <?php endif; ?>

    <?php if (!empty($erro_validacao)): ?>
        <div id="alerta-validacao" class="alert alert-danger">
            <?= validation_errors() ?>
        </div>
    <?php endif; ?>

    <form id="produtoForm" method="post" action="<?= site_url('produtos/salvar') ?>">
        <div class="mb-3">
            <label class="form-label">Nome do Produto:</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Preço:</label>
            <input type="number" step="0.01" name="preco" class="form-control" required>
        </div>

        <div id="variacoes_container">
            <div id="variacoes" class="mb-3">
                <h4>Variações (opcional)</h4>
                <div class="var row g-2 align-items-center mb-2">
                    <div class="col-md-3">
                        <input type="text" name="variacoes[0][nome]" class="form-control var-input" placeholder="Nome da variação">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" name="variacoes[0][preco_extra]" class="form-control var-input" placeholder="Preço extra">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="variacoes[0][estoque]" class="form-control var-input" placeholder="Estoque da variação">
                    </div>
                </div>
            </div>

            <button type="button" onclick="addVar()" id="add_var_btn" class="btn btn-secondary mb-3">+ Adicionar variação</button>
        </div>

        <div class="mb-3" id="estoque_simples_container">
            <label class="form-label">Estoque (caso não use variações):</label>
            <input type="number" name="estoque_simples" id="estoque_simples" class="form-control">
        </div>


        <input type="hidden" name="id" id="produto_id" value=""> 
        <button type="submit" class="btn btn-primary">Salvar Produto</button>
    </form>
</div>

<div id="mensagem"></div>

<div id="produto_salvo" class="row">
    <?php if (!empty($produtos)): ?>
        <?php foreach ($produtos as $produto): ?>
            <div class="col-md-3">
                <div class="card mb-3 produto-card" data-id="<?= $produto->id ?>" style="cursor:pointer; position: relative;">
                    <div class="card-header">
                        <strong><?= $produto->nome ?></strong>
                        <form method="post" action="<?= site_url('produtos/excluir') ?>" style="position: absolute; top: 0px; right: 2px;">
                            <input type="hidden" name="id" value="<?= $produto->id ?>">
                            <button 
                                type="submit" 
                                class="btn btn-sm btn-danger" 
                                onclick="return confirm('Tem certeza que deseja excluir este produto?')"
                                style="padding: 2px 6px; font-weight: bold;"
                            >×</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="produto-imagem-simulada">
                            <span style="font-size: 40px;">👕</span>
                        </div>
                        <p>Preço base: R$ <?= number_format($produto->preco, 2, ',', '.') ?></p>

                        <?php if (!empty($produto->variacoes)): ?>
                            <h5>Variações:</h5>
                            <ul>
                                <?php foreach ($produto->variacoes as $v): ?>
                                    <li>
                                        <?= $v->nome ?> - R$ <?= number_format($v->preco_extra, 2, ',', '.') ?> | 
                                        Estoque: <?= $v->estoque->quantidade ?? 0 ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif (!empty($produto->quantidade)): ?>
                            <p>Estoque: <?= $produto->quantidade->quantidade ?></p>
                        <?php endif; ?>

                         <button class="btn btn-success" onclick="event.stopPropagation(); adicionarAoCarrinho(<?= $produto->id ?>)">Comprar</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function adicionarAoCarrinho(produtoId) {
        $.post('<?= site_url("produtos/adicionar_carrinho") ?>', { produto_id: produtoId }, function(response) {
            if(response.success) {
                alert('Produto adicionado ao carrinho!');
                
                if ($('#badge-carrinho').length) {
                    let count = parseInt($('#badge-carrinho').text());
                    $('#badge-carrinho').text(count + 1);
                } else {
                    $('.cart-icon').append(`
                        <span id="badge-carrinho" class="badge rounded-pill bg-danger cart-badge" style="position: absolute; top: -5px; right: -10px;">
                            1
                        </span>
                    `);
                }
            } else {
                alert('Erro ao adicionar o produto ao carrinho.');
            }
        }, 'json');
    }

    function toggleInputs() {
        let variacoesPreenchidas = false;

        $('.var-input').each(function () {
            if ($(this).val().trim() !== '') {
                variacoesPreenchidas = true;
            }
        });

        const estoqueSimples = $('#estoque_simples').val().trim();

        if (variacoesPreenchidas) {
            $('#estoque_simples_container').hide();
        } else {
            $('#estoque_simples_container').show();
        }

        if (estoqueSimples !== '') {
            $('#variacoes_container').hide();
        } else {
            $('#variacoes_container').show();
        }
    }

    $(document).on('input', '.var-input, #estoque_simples', toggleInputs);

    $(document).ready(function () {
        toggleInputs();

        $('#produto_salvo').on('click', '.produto-card', function () {
            const produtoId = $(this).data('id');

            $.ajax({
                url: '<?= site_url("produtos/get_json") ?>/' + produtoId,
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        preencherFormulario(response.produto);
                    } else {
                        alert('Erro ao carregar dados do produto.');
                    }
                },
                error: function () {
                    alert('Erro na requisição ao servidor.');
                }
            });
        });

        $('#produto_salvo').on('click', '.btn-comprar', function(e) {
            e.stopPropagation();

            const produtoId = $(this).data('id');

            $.post('<?= site_url("carrinho/adicionar_carrinho") ?>', { produto_id: produtoId }, function(response) {
                if(response.success) {
                    alert('Produto adicionado ao carrinho!');
                } else {
                    alert('Erro ao adicionar o produto ao carrinho.');
                }
            }, 'json');
        });

        setTimeout(function () {
            const alertas = ['#alerta-sucesso', '#alerta-erro-duplicado', '#alerta-validacao'];

            alertas.forEach(function (selector) {
                const $el = $(selector);
                if ($el.length) {
                    $el.fadeOut(500, function() {
                        $(this).remove();
                    });
                }
            });
        }, 3000);
    });

    let count = 1;
    function preencherFormulario(produto) {
        $('#produto_id').val(produto.id);
        $('input[name="nome"]').val(produto.nome);
        $('input[name="preco"]').val(produto.preco);

        $('#variacoes').html('<h4>Variações (opcional)</h4>');
        $('#estoque_simples').val('');
        count = 0;

        if (produto.variacoes && produto.variacoes.length > 0) {
            produto.variacoes.forEach(function (v) {
                const $div = $(`
                    <div class="var row g-2 align-items-center mb-2">
                        <div class="col-md-3">
                            <input type="text" name="variacoes[${count}][nome]" class="form-control var-input" value="${v.nome}">
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="variacoes[${count}][preco_extra]" class="form-control var-input" value="${v.preco_extra}">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="variacoes[${count}][estoque]" class="form-control var-input" value="${v.estoque ? v.estoque.quantidade : ''}">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-danger btn-sm btn-remover-var">Remover</button>
                        </div>
                    </div>
                `);
                $('#variacoes').append($div);
                count++;
            });
        } else {
            addVar();
            $('#estoque_simples').val(produto.quantidade ? produto.quantidade.quantidade : '');
        }

        toggleInputs(); 
    }

    function addVar() {
        const $div = $(`
            <div class="var row g-2 align-items-center mb-2">
                <div class="col-md-3">
                    <input type="text" name="variacoes[${count}][nome]" class="form-control var-input" placeholder="Nome da variação">
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="variacoes[${count}][preco_extra]" class="form-control var-input" placeholder="Preço extra">
                </div>
                <div class="col-md-3">
                    <input type="number" name="variacoes[${count}][estoque]" class="form-control var-input" placeholder="Estoque da variação">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-danger btn-sm btn-remover-var">Remover</button>
                </div>
            </div>
        `);
        $('#variacoes').append($div);
        count++;
    }

    $(document).on('click', '.btn-remover-var', function() {
        $(this).closest('.var').remove();
    });
</script>


<?php include('rodape.php'); ?>
</body>
</html>
