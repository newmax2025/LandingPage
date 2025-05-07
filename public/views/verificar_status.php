<?php
$id = $_GET["id"];
$token = "SEU_TOKEN_AQUI";

$ch = curl_init("https://api.virtualpay.io/v2/transaction/$id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $token"
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

$status = $result["status"] ?? "erro";

echo json_encode(["status" => $status]);
