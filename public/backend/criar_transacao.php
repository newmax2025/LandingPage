<?php
$data = json_decode(file_get_contents("php://input"), true);

$email = "bbrjogopublicidades@gmail.com";
$password = "4VI7B5xOEe07uwdTU0d55OY8UN";
$amount = $data["valor"] ?? 0;
$customerId = "6728f0a2cba3ac9ea3009993";

// 1. Login para obter o token
$ch = curl_init("https://api.xgateglobal.com/auth/token");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
  CURLOPT_POSTFIELDS => json_encode([
    "email" => $email,
    "password" => $password
  ])
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
  http_response_code($httpCode);
  echo json_encode(["erro" => "Falha na autenticação"]);
  exit;
}

$token = json_decode($response, true)["token"];

// 2. Buscar moedas
$ch = curl_init("https://api.xgateglobal.com/deposit/company/currencies");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"]
]);

$currencyResponse = curl_exec($ch);
curl_close($ch);
$currencies = json_decode($currencyResponse, true);

// 3. Criar transação
$currency = $currencies[0]; // supondo que seja 'BRL'

$ch = curl_init("https://api.xgateglobal.com/deposit");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
  ],
  CURLOPT_POSTFIELDS => json_encode([
    "amount" => $amount,
    "customerId" => $customerId,
    "currency" => $currency
  ])
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resposta = json_decode($response, true);

if ($httpCode === 200 && isset($resposta["data"]["code"])) {
  echo json_encode([
    "pix" => [
      "qrcode" => $resposta["data"]["code"]
    ],
    "id" => $resposta["data"]["id"],
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
