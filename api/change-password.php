<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

if (!isset($_SESSION['user_id'])) { /* ... verificação de login ... */ }

include_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

// Validações
if (empty($data->currentPassword) || empty($data->newPassword)) { /* ... */ }
if ($data->newPassword === $data->currentPassword) { /* ... */ }

// 1. Busca a senha atual do usuário para verificação
$query_get = "SELECT password_hash FROM users WHERE id = :id";
$stmt_get = $db->prepare($query_get);
$stmt_get->bindParam(':id', $_SESSION['user_id']);
$stmt_get->execute();

$row = $stmt_get->fetch(PDO::FETCH_ASSOC);
$current_password_hash = $row['password_hash'];

// 2. Verifica se a "senha atual" informada está correta
if (password_verify($data->currentPassword, $current_password_hash)) {
    // 3. Se estiver correta, atualiza para a nova senha
    $new_password_hash = password_hash($data->newPassword, PASSWORD_BCRYPT);
    $query_update = "UPDATE users SET password_hash = :new_password_hash WHERE id = :id";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->bindParam(':new_password_hash', $new_password_hash);
    $stmt_update->bindParam(':id', $_SESSION['user_id']);

    if($stmt_update->execute()){
        http_response_code(200);
        echo json_encode(["message" => "Senha alterada com sucesso."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Não foi possível alterar a senha."]);
    }
} else {
    // Senha atual incorreta
    http_response_code(401);
    echo json_encode(["message" => "A senha atual está incorreta."]);
}
?>