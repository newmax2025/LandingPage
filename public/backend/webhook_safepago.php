<?php
require_once "config.php"; // conexão com o BD

// Recebe o JSON bruto do webhook
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verifica se é uma transação paga
if (isset($data['data']['status']) && $data['data']['status'] === 'paid') {
    $codigoTransacao = $data['data']['id'];

    // Busca o cliente correspondente
    $sql = "SELECT cliente_id FROM transacoes WHERE codigo_transacao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $codigoTransacao);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $clienteId = $row['cliente_id'];

        // Ativa a conta (exemplo: muda status do cliente ou libera serviço)
        $sqlUpdate = "UPDATE clientes SET status = 'ativo' WHERE id = ?";
        $stmtUpdate = $conexao->prepare($sqlUpdate);
        $stmtUpdate->bind_param("i", $clienteId);
        $stmtUpdate->execute();
    }
}

// Responde com status 200 para confirmar recebimento
http_response_code(200);
echo json_encode(['status' => 'ok']);
