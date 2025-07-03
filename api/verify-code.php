<?php
session_start(); // Garante que a sessão foi iniciada
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->cpf) || empty($data->code)) {
    http_response_code(400);
    echo json_encode(array("message" => "CPF e código são obrigatórios."));
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id FROM users WHERE cpf = :cpf AND reset_code = :code AND reset_expires_at > NOW() LIMIT 1";
$stmt = $db->prepare($query);

$cpf = htmlspecialchars(strip_tags($data->cpf));
$code = htmlspecialchars(strip_tags($data->code));

$stmt->bindParam(':cpf', $cpf);
$stmt->bindParam(':code', $code);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // Código correto. Limpa o código de reset do banco.
    $update_query = "UPDATE users SET reset_code = NULL, reset_expires_at = NULL WHERE cpf = :cpf";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':cpf', $cpf);
    $update_stmt->execute();

    // --- ADIÇÃO IMPORTANTE ---
    // Cria uma variável de sessão que autoriza o usuário a acessar o próximo passo
    $_SESSION['reset_authorized_for_cpf'] = $cpf;

    http_response_code(200);
    echo json_encode(array("message" => "Código verificado com sucesso."));
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Código inválido ou expirado. Tente novamente."));
}
