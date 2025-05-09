<?php
require_once "config.php"; // conexão com o BD

// Recebe o JSON bruto do webhook
$json = file_get_contents('php://input');

// Decodifica o JSON
$data = json_decode($json, true);

// Verifica se o status é 'PAID'
if (isset($data['status']) && strtoupper($data['status']) === 'PAID') {
    $codigoTransacao = $data['id'];

    // Busca o cliente correspondente usando o código da transação
    $sql = "SELECT cliente_id FROM transacoes WHERE codigo_transacao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $codigoTransacao);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $clienteId = $row['cliente_id'];

        // Ativa a conta do cliente
        $sqlUpdate = "UPDATE clientes SET status = 'ativo' WHERE id = ?";
        $stmtUpdate = $conexao->prepare($sqlUpdate);
        $stmtUpdate->bind_param("i", $clienteId);
        $stmtUpdate->execute();
    }
}

// Responde com status 200 para confirmar recebimento
http_response_code(200);
echo json_encode(['status' => 'ok']);
