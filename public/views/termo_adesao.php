<?php
session_start();
if (isset($_GET['v'])) {
    $_SESSION['vendedor_id'] = $_GET['v'];
}

$plano = $_GET['plano'] ?? null;
if (!$plano) {
    header('Location: landing_page.php'); // redireciona se plano for inválido
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Termo de Adesão</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/termo_adesao.css?v=<?php echo md5_file('../assets/css/termo_adesao.css'); ?>">
</head>
<body>

<div class="container">
  <h2>Termo de Responsabilidade – Utilização da Plataforma Newmax Buscas</h2>

  <div id="termo">
    <p><strong>EMPRESA:</strong> Newmax Buscas</p>
    <p><strong>MEIO DE PAGAMENTO:</strong> NewmaxPay</p>
    <p><strong>GATEWAY DE PAGAMENTO:</strong> Integração via API</p>
    <p>Ao realizar o pagamento por meio do sistema NewmaxPay, o CLIENTE declara estar ciente e de pleno acordo com as condições abaixo, estabelecendo vínculo contratual com a empresa Newmax Buscas, nos termos da legislação brasileira aplicável.</p>

    <p><strong>1. DO ACESSO E CONSUMO IMEDIATO DO SERVIÇO</strong><br>
    Após a confirmação do pagamento, o CLIENTE terá acesso imediato à plataforma e aos serviços contratados, sendo estes considerados consumidos a partir do momento da liberação do login ou acesso.<br>
    Nos termos do Art. 49, §1º do Código de Defesa do Consumidor (Lei nº 8.078/90), o direito de arrependimento não se aplica a serviços com execução instantânea ou produtos digitais de uso imediato, como é o caso da Newmax Buscas.</p>

    <p><strong>2. DA NÃO POSSIBILIDADE DE CANCELAMENTO APÓS PAGAMENTO</strong><br>
    O CLIENTE reconhece que, ao realizar o pagamento e acessar o sistema, inicia-se imediatamente a prestação de serviço. Assim, não será possível o cancelamento ou estorno do valor pago, uma vez que o serviço passa a ser utilizado de forma automática.<br>
    Conforme o Art. 428, inciso III do Código Civil, considera-se nulo o arrependimento de contrato quando o objeto já foi consumido. Além disso, nos termos do Decreto 7.962/13 (Lei do E-commerce), o consumidor deve ser informado de forma clara antes da contratação, o que é feito neste termo.</p>

    <p><strong>3. DA POSSIBILIDADE DE MANUTENÇÕES TÉCNICAS</strong><br>
    A empresa se reserva o direito de realizar manutenções programadas ou emergenciais em determinados módulos da plataforma, garantindo sempre a estabilidade, segurança e aprimoramento contínuo do sistema.<br>
    Essa condição está amparada pelo Marco Civil da Internet (Lei nº 12.965/14, Art. 7º, inciso V), que reconhece a possibilidade de suspensão temporária de serviços digitais por motivos técnicos, sem prejuízo à boa-fé do fornecedor.</p>

    <p><strong>4. DA BOA-FÉ E TRANSPARÊNCIA</strong><br>
    O CLIENTE afirma ter ciência plena sobre a natureza do serviço, seus recursos e funcionamento, confirmando que não está sendo induzido ao erro. O aceite deste termo representa compromisso com os princípios da boa-fé contratual (art. 422 do Código Civil) e respeito mútuo entre as partes.</p>

    <p><strong>5. DO COMBATE A FRAUDES</strong><br>
    Este termo foi instituído como medida de segurança e prevenção de fraudes, garantindo que a Newmax Buscas possa operar com confiabilidade e proteção contra tentativas de uso indevido da política de cancelamento.</p>

    <p><strong>Newmax Buscas</strong> – Compromisso com a legalidade, segurança digital e transparência nas relações com seus clientes.<br>
    Este documento possui validade jurídica, podendo ser apresentado em caso de contestação ou disputa.</p>
  </div>

<button id="aceitar" disabled>Aceito os Termos</button>
  </div>

  <script>
    const termo = document.getElementById('termo');
    const botao = document.getElementById('aceitar');

    termo.addEventListener('scroll', () => {
      if (termo.scrollTop + termo.clientHeight >= termo.scrollHeight - 5) {
        botao.classList.add('ativo');
        botao.disabled = false;
      }
    });

    botao.addEventListener('click', () => {
  if (botao.classList.contains('ativo')) {
    window.location.href = 'pagamento.php?plano=<?= $plano ?>';
  }
});

  </script>

</body>
</html>
