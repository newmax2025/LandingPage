<?php
$data = json_decode(file_get_contents("php://input"), true);

$payload = [
  "customer" => [
    "name" => "João Silva",
    "email" => "teste@example.com",
    "phone" => "11999999999"
  ],
  "paymentMethod" => "PIX",
  "installments" => 1,
  "amount" => $data["valor"],
  "items" => [[
    "title" => "Depósito via PIX",
    "unitPrice" => $data["valor"],
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
http_response_code(curl_getinfo($ch, CURLINFO_HTTP_CODE));
echo $response;
curl_close($ch);
?>
