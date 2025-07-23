<!DOCTYPE html>
<html>
<head>
    <title>Pedido Finalizado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .checkmark {
            font-size: 4rem;
            color: #28a745;
        }
    </style>
</head>
<body>
<?php include('header.php'); ?>

<div class="container text-center mt-5">
    <div class="checkmark mb-3">
        ✅
    </div>
    <h2 class="text-success">Pedido finalizado com sucesso!</h2>

    <?php if ($this->session->flashdata('sucesso')): ?>
        <p class="mt-3 alert alert-success"><?= $this->session->flashdata('sucesso') ?></p>
    <?php endif; ?>

    <a href="<?= base_url('produtos') ?>" class="btn btn-primary mt-4">Voltar</a>
</div>

<?php include('rodape.php'); ?>

</body>
</html>
