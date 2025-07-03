<?php
session_start();
header("Access-Control-Allow-Origin: *"); // Em produção, restrinja para o seu domínio
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Não autorizado."]);
    exit();
}

include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

// Valida os dados recebidos (exemplo: e-mail)
if (empty($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "E-mail inválido."]);
    exit();
}

// Atualiza os dados no banco
$query = "UPDATE users SET email = :email, phone = :phone WHERE id = :id";
$stmt = $db->prepare($query);

$stmt->bindParam(':email', $data->email);
$stmt->bindParam(':phone', $data->phone);
$stmt->bindParam(':id', $_SESSION['user_id']);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Perfil atualizado com sucesso."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Não foi possível atualizar o perfil."]);
}
