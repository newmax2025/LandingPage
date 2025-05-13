<?php
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

session_start();
if (isset($_GET['v'])) {
    $_SESSION['vendedor_id'] = $_GET['v'];
}

$planoSelecionado = $_GET['plano'] ?? null;

$planos = [
    '120' => 'Plano Simples: R$120,00',
    '200' => 'Plano Básico: R$200,00',
    '300' => 'Plano Premium: R$300,00',
    '650' => 'Plano Diamante: R$650,00',
    '2520' => 'Plano Premium Anual: R$2.520,00 (30% off)',
    '3120' => 'Plano Diamante Anual: R$3.120,00 (40% off)',
    '500' => 'Plano Revendedor: R$500,00',
];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro e Pagamento</title>
    <link rel="stylesheet" href="../assets/css/pagamentos.css?v=<?php echo md5_file('../assets/css/pagamentos.css'); ?>">
</head>
<body>

<div id="registrationContainer" class="container" style="background-color: #000d1e;">
    <img src="../assets/img/New Max Buscas.png" alt="Logo do Cliente" class="logo">
    <h1>Cadastro de Usuário</h1>
    <form id="registrationForm">
        <div class="form-group">
            <label for="username">Usuário:</label>
            <input type="text" id="username" pattern="^[a-zA-Z0-9_]{3,20}$" placeholder="Digite seu nome de usuário" required>
        </div>
        <div class="form-group">
            <label for="password">Senha:</label>
            <input type="password" id="password" pattern="^[a-zA-Z0-9_]{3,20}$" placeholder="Digite sua senha" required>
        </div>
        <button type="submit" id="registerButton">Cadastrar</button>
        <div id="registrationMessage" class="message-area"></div>
        <div class="copy">
            <p>New Max Pay | Tecnologia de Pagamento</p>
        </div>
    </form>
</div>

<div id="paymentContainer" class="container" style="display: none;">
    <img src="../assets/img/New Max Buscas.png" alt="Logo do Cliente" class="logo">
    <h1>Pagamento</h1>
    <label>Plano selecionado:</label>
    <select id="dep_valor">
        <?php if (isset($planos[$planoSelecionado])): ?>
            <option value="<?= $planoSelecionado ?>"><?= $planos[$planoSelecionado] ?></option>
        <?php else: ?>
            <?php foreach ($planos as $valor => $nome): ?>
                <option value="<?= $valor ?>"><?= $nome ?></option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>

    <button id="depositButton">Assinar Plano</button>
    <div id="dep_result"></div>
    <div class="copy">
        <p>New Max Pay | Tecnologia de Pagamento</p>
    </div>
    <p id="loggedInUser" style="margin-top: 15px; font-size: 0.9em;"></p>
</div>

<script>
    const vendedorId = <?= json_encode($_SESSION['vendedor_id'] ?? null); ?>;

	document.getElementById('depositButton').addEventListener('click', function () {
        const selectedValue = document.getElementById('dep_valor').value;
        if (selectedValue === '2520' || selectedValue === '3120') {
			statusForm(0);
    window.location.href = 'usdt_pagamento.php?plano=' + selectedValue;
}

    });
</script>
<script src="../assets/js/pagamento.js?v=<?php echo md5_file('../assets/js/pagamento.js'); ?>"></script>

</body>
</html>
