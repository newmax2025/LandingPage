<?php
require_once "config.php"; // conexão com o BD

// Recebe o JSON bruto do webhook
$json = file_get_contents('php://input');

// Salva o conteúdo recebido em um arquivo para análise
file_put_contents('log_webhook.txt', date("Y-m-d H:i:s") . "\n" . $json . "\n\n", FILE_APPEND);

// Tenta decodificar o JSON
$data = json_decode($json, true);

// Aqui você pode continuar com a lógica antiga, se desejar
if (isset($data['data']['status']) && $data['data']['status'] === 'paid') {
    $codigoTransacao = $data['data']['id'];

    $sql = "SELECT cliente_id FROM transacoes WHERE codigo_transacao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $codigoTransacao);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $clienteId = $row['cliente_id'];

        $sqlUpdate = "UPDATE clientes SET status = 'ativo' WHERE id = ?";
        $stmtUpdate = $conexao->prepare($sqlUpdate);
        $stmtUpdate->bind_param("i", $clienteId);
        $stmtUpdate->execute();
    }
}

// Sempre responda com status 200
http_response_code(200);
echo json_encode(['status' => 'ok']);
