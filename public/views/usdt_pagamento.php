<?php
$planos = [
    '2520' => 'Plano Premium Anual: R$2.520,00 (30% off)',
    '3120' => 'Plano Diamante Anual: R$3.120,00 (40% off)',
];

$planoSelecionado = $_GET['plano'] ?? null;
$nomePlano = $planos[$planoSelecionado] ?? 'Plano USDT';

// Consulta cotação do dólar
$url = "https://economia.awesomeapi.com.br/json/last/USD-BRL";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$resposta = curl_exec($ch);
curl_close($ch);
$data = json_decode($resposta, true);
$cotacaoVenda = $data['USDBRL']['ask'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($nomePlano) ?> - USDT</title>
  <link rel="stylesheet" href="../assets/css/credito_admin.css?v=<?php echo md5_file('../assets/css/credito_admin.css'); ?>">
</head>
<body>
  <div class="container">
    <img src="../assets/img/qr_polygon.jpg" alt="QR Code USDT Polygon" style="max-width: 200px;">

    <h2><?= htmlspecialchars($nomePlano) ?></h2>

    <div class="endereco" id="walletAddress">
      0x0D8F4c21d6493D79970daaf55339EF304Efa5D6A
    </div>

    <button class="copiar-btn" onclick="copiarEndereco()">Copiar Endereço</button>

    <div class="rede">
      Rede: <strong>Polygon</strong>
    </div>

    <div class="alerta">
      Certifique-se de depositar <strong>USDT da Polygon</strong> para este endereço.
    </div>

    <p><strong>Cotação atual do dólar:</strong><br>1 USD =
      <span id="cotacao"><?= $cotacaoVenda ? number_format($cotacaoVenda, 4, ',', '.') : 'Indisponível' ?> BRL</span>
    </p>

    <?php if ($cotacaoVenda): ?>
    <table>
      <thead>
        <tr>
          <th>Valor em BRL</th>
          <th>Equivalente em USDT</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $valores = [$planoSelecionado];
          foreach ($valores as $brl) {
              $bonusPercentual = ($brl >= 3000) ? 20 : 10;
              $bonus = $brl * ($bonusPercentual / 100);
              $totalComBonus = $brl + $bonus;
              $usdt = $brl / $cotacaoVenda;

              echo "<tr>
                      <td>R$" . number_format($brl, 2, ',', '.') . "</td>
                      <td>≈ " . number_format($usdt, 2) . " USDT</td>
                    </tr>";
          }
        ?>
      </tbody>
    </table>
    <?php else: ?>
      <p style="color: #ffcc00;">Não foi possível obter a cotação do dólar no momento.</p>
    <?php endif; ?>
  </div>

  <script>
    function copiarEndereco() {
      const texto = document.getElementById("walletAddress").innerText;
      navigator.clipboard.writeText(texto)
        .then(() => alert("Endereço copiado com sucesso!"))
        .catch(() => alert("Erro ao copiar o endereço."));
    }
  </script>
</body>
</html>
