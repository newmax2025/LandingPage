<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

echo "<pre>[STEP 1] Dados recebidos: " . json_encode($data) . "</pre>"; flush();

// Verificação de entrada
if (!is_array($data) || !isset($data["valor"]) || !is_numeric($data["valor"]) || $data["valor"] <= 0) {
    http_response_code(400);
    echo "<pre>[ERRO] Valor inválido: " . json_encode($data["valor"]) . "</pre>"; flush();
    echo json_encode(["erro" => "Valor inválido para transação."]);
    exit;
}

$email = "bbrjogopublicidades@gmail.com";
$password = "4VI7B5xOEe07uwdTU0d55OY8UN";
$amount = floatval($data["valor"]);
$url_api = "https://api.xgateglobal.com";

echo "<pre>[STEP 2] Iniciando autenticação com email: $email</pre>"; flush();

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

echo "<pre>[STEP 3] Resposta da autenticação: HTTP $httpCode - $response</pre>"; flush();

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo "<pre>[ERRO] Falha na autenticação</pre>"; flush();
    echo json_encode(["erro" => "Falha na autenticação"]);
    exit;
}

$token = json_decode($response, true)["token"] ?? null;
if (!$token) {
    http_response_code(500);
    echo "<pre>[ERRO] Token não recebido.</pre>"; flush();
    echo json_encode(["erro" => "Token não recebido"]);
    exit;
}

echo "<pre>[STEP 4] Token recebido: $token</pre>"; flush();

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

echo "<pre>[STEP 5] Resposta currencies: HTTP $httpCode - $response</pre>"; flush();

$currencies = json_decode($response, true);
if (!is_array($currencies) || empty($currencies)) {
    http_response_code(500);
    echo "<pre>[ERRO] Nenhuma moeda retornada</pre>"; flush();
    echo json_encode(["erro" => "Nenhuma moeda retornada"]);
    exit;
}

$currency = $currencies[0];
echo "<pre>[STEP 6] Moeda selecionada: " . json_encode($currency) . "</pre>"; flush();

// 3. Criar depósito
$customerId = "6728f0a2cba3ac9ea3009993";
$depositData = json_encode([
    "amount" => $amount,
    "customerId" => $customerId,
    "currency" => $currency
]);

echo "<pre>[STEP 7] Dados do depósito enviados: $depositData</pre>"; flush();

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

echo "<pre>[STEP 8] Resposta do depósito: HTTP $httpCode - $response</pre>"; flush();

$resposta = json_decode($response, true);

if ($httpCode === 200 && isset($resposta["data"]["code"])) {
    echo "<pre>[SUCCESS] PIX criado com sucesso. ID: " . $resposta["data"]["id"] . "</pre>"; flush();
    echo json_encode([
        "code"   => $resposta["data"]["code"],
        "pix"    => ["qrcode" => $resposta["data"]["code"]],
        "id"     => $resposta["data"]["id"],
        "amount" => $amount,
        "status" => $resposta["data"]["status"] ?? "PENDING"
    ]);
} else {
    http_response_code($httpCode);
    $msgErro = $resposta["message"] ?? "Erro desconhecido na criação do PIX";
    echo "<pre>[ERRO] Falha na criação do PIX: $msgErro</pre>"; flush();
    echo json_encode(["erro" => $msgErro]);
}
?>
