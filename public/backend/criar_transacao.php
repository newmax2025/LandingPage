<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

// Verificação de entrada
if (!is_array($data) || !isset($data["valor"]) || !is_numeric($data["valor"]) || $data["valor"] <= 0) {
    http_response_code(400);
    echo json_encode(["erro" => "Valor inválido para transação."]);
    exit;
}

$email = "bbrjogopublicidades@gmail.com";
$password = "4VI7B5xOEe07uwdTU0d55OY8UN";
$amount = floatval($data["valor"]);
$url_api = "https://api.xgateglobal.com";

// 1. Autenticação
$loginData = json_encode(["email" => $email, "password" => $password]);

$ch = curl_init("$url_api/auth/token");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => $loginData
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(["erro" => "Falha na autenticação"]);
    exit;
}

$token = json_decode($response, true)["token"] ?? null;
if (!$token) {
    http_response_code(500);
    echo json_encode(["erro" => "Token não recebido"]);
    exit;
}

// 2. Buscar currencies
$ch = curl_init("$url_api/deposit/company/currencies");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token"
    ]
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$currencies = json_decode($response, true);
if (!is_array($currencies) || empty($currencies)) {
    http_response_code(500);
    echo json_encode(["erro" => "Nenhuma moeda retornada"]);
    exit;
}

$currency = $currencies[0]; // Agora usamos o objeto inteiro

// 3. Criar depósito
$customerId = "6728f0a2cba3ac9ea3009993";
$depositData = json_encode([
    "amount" => $amount,
    "customerId" => $customerId,
    "currency" => $currency
]);

$ch = curl_init("$url_api/deposit");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => $depositData
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resposta = json_decode($response, true);

if ($httpCode === 200 && isset($resposta["data"]["code"])) {
    echo json_encode([
        "code"   => $resposta["data"]["code"],
        "pix"    => ["qrcode" => $resposta["data"]["code"]],
        "id"     => $resposta["data"]["id"],
        "amount" => $amount,
        "status" => $resposta["data"]["status"] ?? "PENDING"
    ]);
} else {
    http_response_code($httpCode);
    echo json_encode([
        "erro" => $resposta["message"] ?? "Erro desconhecido na criação do PIX"
    ]);
}
?>
