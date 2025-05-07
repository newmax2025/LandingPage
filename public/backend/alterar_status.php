<?php
header('Content-Type: application/json');
ob_start();

require 'config.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException("Erro ao decodificar JSON.");
    }

    if (!isset($data["username"]) || !isset($data["status"]) || !isset($data["amount"])) {
        throw new InvalidArgumentException("Campos 'username' e 'status' são obrigatórios.");
    }

    $username = trim($data["username"]);
    $status = trim($data["status"]);
    $amount = $data["amount"] ?? null;
	$codigoTransacao = trim($data["transacaoId"]);

    // Define o plano e validade
    $dias_validade = 30; // padrão mensal
    switch ((int)$amount) {
        case 120: $plano = "Simples"; $id_plano = 1; break;
        case 200: $plano = "Basico"; $id_plano = 2; break;
        case 300: $plano = "Premium"; $id_plano = 3; break;
        case 650: $plano = "Diamante"; $id_plano = 4; break;
        case 2520: $plano = "Premium_Anual"; $id_plano = 5; $dias_validade = 365; break;
        case 3120: $plano = "Diamante_Anual"; $id_plano = 6; $dias_validade = 365; break;
        case 500: $plano = "Revendedor"; $dias_validade = null; $id_plano = null; break;
        default: $plano = "Desconhecido"; $dias_validade = null; $id_plano = null; break;
    }

    if (empty($username) || empty($status)) {
        echo json_encode(["success" => false, "message" => "Preencha todos os campos!"]);
        exit();
    }

    $sqlCheck = "SELECT id FROM clientes WHERE usuario = ?";
    $stmtCheck = $conexao->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $username);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        $stmtCheck->close();
        echo json_encode(["success" => false, "message" => "Usuário não encontrado."]);
        exit();
    }
    $cliente = $resultCheck->fetch_assoc();
	$cliente_id = $cliente['id'];
	$stmtCheck->close();


    // Calcula data de expiração se aplicável
    $data_expira = null;
    if ($dias_validade !== null) {
        $data_expira = (new DateTime())->modify("+$dias_validade days")->format("Y-m-d");
    }

    // Atualiza dados
    $sqlUpdate = "UPDATE clientes SET status = ?, plano = ?, data_last_pg = CURDATE(), data_expira = ?, plano_id = ? WHERE usuario = ?";
    $stmtUpdate = $conexao->prepare($sqlUpdate);

    if ($stmtUpdate === false) {
        throw new RuntimeException("Erro ao preparar a atualização: " . $conexao->error);
    }

    $stmtUpdate->bind_param("sssss", $status, $plano, $data_expira, $id_plano, $username );

    $updateSuccess = false;
	if ($stmtUpdate->execute()) {
    	$updateSuccess = true;
	}
	$stmtUpdate->close();

	 // Insere transação
    $tipoTransacao = 'plano';
    $sqlTransacao = "INSERT INTO transacoes (codigo_transacao, cliente_id, tipo, valor) VALUES (?, ?, ?, ?)";
    $stmtTransacao = $conexao->prepare($sqlTransacao);
    $stmtTransacao->bind_param("siss", $codigoTransacao, $cliente_id, $tipoTransacao, $amount);
    $stmtTransacao->execute();
    $stmtTransacao->close();

    if ($updateSuccess) {
    	echo json_encode(["success" => true, "message" => "Status e transação registrados com sucesso."]);
	} else {
    	echo json_encode(["success" => false, "message" => "Erro ao atualizar status."]);
	}

} catch (mysqli_sql_exception $e) {
    ob_end_clean();
    error_log("Erro MySQL (alterar_status.php): " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Erro interno no servidor [DB]."]);

} catch (InvalidArgumentException $e) {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);

} catch (Exception $e) {
    ob_end_clean();
    error_log("Erro Geral (alterar_status.php): " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Erro interno no servidor."]);

} finally {
    ob_end_flush();
}
?>
