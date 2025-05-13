<?php
require 'config.php';

$vendedorId = $_GET['v'] ?? null;

if (!$vendedorId) {
    echo json_encode(['erro' => 'Vendedor não especificado']);
    exit;
}

$sql = "SELECT whatsapp FROM vendedores WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $vendedorId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $dados = $result->fetch_assoc();
    $whatsapp = preg_replace('/\D/', '', $dados['whatsapp']); // limpa o número
    echo json_encode(['whatsapp' => 'https://wa.me/' . $whatsapp]);
} else {
    echo json_encode(['erro' => 'Vendedor não encontrado']);
}

$stmt->close();
$conexao->close();
?>
