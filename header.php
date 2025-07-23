<?php
    $carrinho = $this->session->userdata('carrinho');
    $total_itens_carrinho = 0;

    if (is_array($carrinho)) {
        foreach ($carrinho as $item) {
            $total_itens_carrinho += $item['quantidade'] ?? 0;
        }
    } else {
        $carrinho = [];
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>StoreShirt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .cart-icon {
            position: relative;
            cursor: pointer;
            margin-left: 15px;
        }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
<header class="bg-dark text-white p-3 sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h4 mb-0">
            <a href="<?= base_url('produtos') ?>" class="text-white text-decoration-none">StoreShirt</a>
        </h1>
        <div class="d-flex align-items-center">
            <a href="<?= base_url('produtos/salvar'); ?>" class="btn btn-primary">Cadastrar Produto</a>
            <a href="<?= base_url('cupons'); ?>" class="btn btn-warning ms-3">Cupons</a>

            <a href="<?= base_url('carrinho/ver'); ?>" class="cart-icon text-white" title="Carrinho" style="position: relative;">
                <img src="https://cdn-icons-png.flaticon.com/512/1170/1170678.png" width="30" height="30" alt="Carrinho" style="filter: invert(1);">
                <?php if ($total_itens_carrinho > 0): ?>
                    <span id="badge-carrinho" class="badge rounded-pill bg-danger cart-badge" style="position: absolute; top: -5px; right: -10px;">
                        <?= $total_itens_carrinho ?>
                        <span class="visually-hidden">itens no carrinho</span>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<div class="container" style="max-width: 960px;"> <!-- abertura -->
