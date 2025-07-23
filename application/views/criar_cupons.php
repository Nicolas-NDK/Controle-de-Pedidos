<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
      .form-wrapper {
        display: flex;
        justify-content: center;
        padding: 20px;
      }

      form {
        width: 100%;
        max-width: 450px;
      }

      form input.form-control {
        max-width: 450px;
        width: 100%;
      }

      .mb-3 {
        margin-bottom: 1.2rem;
      }

      label {
        display: block;
        margin-bottom: 0.3rem;
      }

      .form-check-label {
        margin-left: 0.3rem;
      }

      .btn {
        margin-right: 10px;
      }

      h2 {
        text-align: center;
        margin-bottom: 1.5rem;
      }
    </style>
</head>
<body>

<?php include('header.php'); ?>

<div class="container mt-4">
    <h2>Criar Cupom</h2>

    <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>

    <div class="form-wrapper">
        <form method="post" action="<?= base_url('cupons/criar') ?>">
            <div class="mb-3">
                <label for="codigo">Código</label>
                <input id="codigo" type="text" name="codigo" class="form-control" value="<?= set_value('codigo') ?>" required>
            </div>

            <div class="mb-3">
                <label>Tipo de Desconto</label><br>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleTipoDesconto">
                    <label class="form-check-label" for="toggleTipoDesconto">Fixo/Percentual</label>
                </div>
            </div>

            <div class="mb-3" id="campo_valor">
                <label for="desconto_valor">Desconto Fixo (R$)</label>
                <input id="desconto_valor" type="number" step="0.01" name="desconto_valor" class="form-control" value="<?= set_value('desconto_valor') ?>">
            </div>

            <div class="mb-3 d-none" id="campo_percentual">
                <label for="desconto_percentual">Desconto Percentual (%)</label>
                <input id="desconto_percentual" type="number" step="0.01" name="desconto_percentual" class="form-control" value="<?= set_value('desconto_percentual') ?>">
            </div>

            <div class="mb-3">
                <label for="validade">Validade</label>
                <input id="validade" type="date" name="validade" class="form-control" value="<?= set_value('validade') ?>" required>
            </div> 

            <div class="mb-3">
                <label for="minimo_subtotal">Subtotal Mínimo (R$)</label>
                <input id="minimo_subtotal" type="number" step="0.01" name="minimo_subtotal" class="form-control" value="<?= set_value('minimo_subtotal', 0) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Criar Cupom</button>
            <a href="<?= base_url('cupons') ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(function() {
        $('#toggleTipoDesconto').on('change', function() {
            if ($(this).is(':checked')) {
                $('#campo_valor').addClass('d-none');
                $('#campo_percentual').removeClass('d-none');
            } else {
                $('#campo_valor').removeClass('d-none');
                $('#campo_percentual').addClass('d-none');
            }
        });
    });
</script>

<?php include('rodape.php'); ?>

</body>
</html>
