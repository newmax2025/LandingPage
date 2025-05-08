<?php
$data = json_decode(file_get_contents("php://input"), true);

$valor = $data["valor"] ?? 0;

$payload = [
  "customer" => [
    "name" => $data["nome"] ?? "Cliente",
    "email" => "teste@example.com",
    "phone" => "11999999999"
  ],
  "paymentMethod" => "PIX",
  "installments" => 1,
  "amount" => $valor,
  "items" => [[
    "title" => "Depósito via PIX",
    "unitPrice" => $valor,
    "quantity" => 1
  ]],
  "pix" => [ "expiresInDays" => 1 ]
];

$ch = curl_init("https://api.safepagoficial.com/functions/v1/transactions");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "Accept: application/json",
    "Content-Type: application/json",
    "Authorization: Basic c2tfbGl2ZV9DREY2bTZWZWVDdVBPR0w4Y2ZHbFBkWTY3YWltNlUyNGZKd0hHSG5pVlVHT1VZMkg6eA=="
  ],
  CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);

$resposta = json_decode($response, true);

if (isset($resposta["data"]["code"])) {
  echo json_encode([
    "pix" => [
      "qrcode" => $resposta["data"]["code"]
    ],
    "id" => $resposta["data"]["id"],
    "amount" => $valor,
    "status" => $resposta["data"]["status"] ?? "PENDING"
  ]);
} else {
  echo json_encode([
    "erro" => $resposta["message"] ?? "Erro desconhecido na criação do PIX."
  ]);
}
?>
