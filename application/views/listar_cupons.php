<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body>

<?php include('header.php'); ?>

<div class="container mt-4">
    <h2>Cupons</h2>
    <a href="<?= base_url('cupons/criar') ?>" class="btn btn-success mb-3">Novo Cupom</a>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Código</th>
                <th>Desconto</th>
                <th>Validade</th>
                <th>Subtotal Mínimo</th>
                <th>Ativo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $hoje = date('Y-m-d');
            foreach ($cupons as $cupom): 
                $is_valido = (isset($cupom->validade) && $cupom->validade >= $hoje);
            ?>
            <tr>
                <td><?= htmlspecialchars($cupom->codigo) ?></td>
                <td>
                    <?php if ($cupom->desconto_percentual): ?>
                        <?= number_format($cupom->desconto_percentual, 2, ',', '.') ?>%
                    <?php elseif ($cupom->desconto_valor): ?>
                        R$ <?= number_format($cupom->desconto_valor, 2, ',', '.') ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="<?= $is_valido ? 'table-success' : 'table-danger' ?>">
                    <?= date('d/m/Y', strtotime($cupom->validade)) ?>
                </td>
                <td>R$ <?= number_format($cupom->minimo_subtotal, 2, ',', '.') ?></td>
                <td><?= $cupom->ativo ? 'Sim' : 'Não' ?></td>
                <td>
                    <a href="<?= base_url('cupons/deletar/'.$cupom->id) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Confirma exclusão?')">Excluir</a>

                    <?php if ($cupom->ativo): ?>
                        <a href="<?= base_url('cupons/toggle_ativo/'.$cupom->id) ?>" class="btn btn-warning btn-sm" onclick="return confirm('Deseja desativar este cupom?')">Desativar</a>
                    <?php else: ?>
                        <a href="<?= base_url('cupons/toggle_ativo/'.$cupom->id) ?>" class="btn btn-success btn-sm" onclick="return confirm('Deseja ativar este cupom?')">Ativar</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include('rodape.php'); ?>
</body>
</html>
