<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body>

<?php include('header.php'); ?>

<?php if ($this->session->flashdata('sucesso')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('sucesso') ?></div>
<?php endif; ?>

<?php if ($this->session->flashdata('erro')): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <?= $this->session->flashdata('erro') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>


<div class="container mt-4">
    <h2>Carrinho de Compras</h2>

    <?php if (!empty($carrinho) && is_array($carrinho)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Qtd</th>
                    <th>Total</th>
                </tr>
            </thead>
            <a href="<?= base_url('carrinho/limpar') ?>" class="btn btn-danger mt-4">Limpar Carrinho</a>
            <tbody>
                <?php foreach ($carrinho as $item): ?>
                    <tr>
                        <td><?= $item['nome'] ?></td>
                        <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                        <td>
                            <?= $item['quantidade'] ?>

                            <a href="<?= base_url('carrinho/remover_carrinho/' . $item['produto_id']) ?>" 
                            title="Remover uma unidade" 
                            style="color: #dc3545; text-decoration: none; margin-left: 5px; font-weight: bold;">
                            &minus;
                            </a>
                        </td>
                        <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr>
        <p><strong>Subtotal:</strong> R$ <?= number_format($subtotal, 2, ',', '.') ?></p>
        <p><strong>Frete:</strong> R$ <?= number_format($frete, 2, ',', '.') ?></p>
        <p><strong>Total:</strong> R$ <?= number_format($total, 2, ',', '.') ?></p>

        <form id="form-cupom" class="mb-4">
            <div class="input-group">
                <input type="text" name="codigo_cupom" id="codigo_cupom" class="form-control" placeholder="Digite o código do cupom" style="max-width: 220px;">
                <button type="submit" class="btn btn-success">Aplicar Cupom</button>
            </div>
        </form>

        <div id="mensagem-cupom"></div>

        <?php if (isset($desconto) && $desconto > 0): ?>
            <p><strong>Desconto:</strong> -R$ <?= number_format($desconto, 2, ',', '.') ?></p>
        <?php endif; ?>

        <p><strong>Total com desconto:</strong> R$ <?= number_format(($total - ($desconto ?? 0)), 2, ',', '.') ?></p>

        <form id="form-endereco" class="row g-3 mt-4 mb-4" action="<?= base_url('carrinho/finalizar_pedido') ?>" method="post">
            <div class="col-md-3">
                <label for="cep" class="form-label">CEP</label>
                <input type="text" class="form-control" id="cep" name="cep" placeholder="00000-000" maxlength="9" autocomplete="off" required>
            </div>
            <div class="col-md-5">
                <label for="rua" class="form-label">Rua</label>
                <input type="text" class="form-control" id="rua" name="rua" placeholder="Rua" readonly required>
            </div>
            <div class="col-md-4">
                <label for="cidade" class="form-label">Cidade</label>
                <input type="text" class="form-control" id="cidade" name="cidade" placeholder="Cidade" readonly required>
            </div>
            <div class="col-md-2">
                <label for="numero" class="form-label">Número</label>
                <input type="text" class="form-control" id="numero" name="numero" placeholder="Número" autocomplete="off" required>
            </div>
            <div class="col-md-4">
                <label for="complemento" class="form-label">Complemento</label>
                <input type="text" class="form-control" id="complemento" name="complemento" placeholder="Complemento" autocomplete="off">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu email" autocomplete="off" required>
            </div>

            <div class="col-md-12">
                <input type="hidden" name="total_com_desconto" value="<?= isset($total) && isset($desconto) ? number_format(($total - $desconto), 2, '.', '') : '0' ?>">
                <button type="submit" class="btn btn-primary mt-3">Finalizar Pedido</button>
            </div>
        </form>

        <div id="mensagem-pedido" class="mt-3"></div>

    <?php else: ?>
        <div class="alert alert-info">Seu carrinho está vazio.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#cep').on('blur', function() {
        let cep = $(this).val().replace(/\D/g, '');
        if (cep.length !== 8) {
            alert('CEP inválido.');
            $('#rua').val('');
            $('#cidade').val('');
            return;
        }

        $.get(`https://viacep.com.br/ws/${cep}/json/`, function(response) {
            if (response.erro) {
                alert('CEP não encontrado.');
                $('#rua').val('');
                $('#cidade').val('');
            } else {
                $('#rua').val(response.logradouro);
                $('#cidade').val(response.localidade);
            }
        });
    });

    $('#form-cupom').on('submit', function(e) {
        e.preventDefault();

        let codigo = $('#codigo_cupom').val().trim();

        if (!codigo) {
            alert('Digite um código de cupom.');
            return;
        }

        $.ajax({
            url: '<?= base_url('carrinho/aplicar_cupom') ?>',
            method: 'POST',
            dataType: 'json',
            data: { codigo_cupom: codigo },
            success: function(res) {
                if (res.success) {
                    $('#mensagem-cupom').html('<div class="alert alert-success">' + res.message + '</div>');
                    location.reload();
                } else {
                    $('#mensagem-cupom').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            },
            error: function() {
                alert('Erro na requisição, tente novamente.');
            }
        });
    });
</script>

<?php include('rodape.php'); ?>
</body>
</html>
