<?php
// Inicia a sessão
session_start();

// Headers da API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->cpf) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "CPF e senha são obrigatórios."));
    exit();
}

$database = new Database();
$db = $database->getConnection();

// --- CORREÇÃO APLICADA AQUI ---
// Removemos a verificação 'AND is_email_verified = TRUE', pois ela não é mais necessária e a coluna foi removida.
$query = "SELECT id, cpf, full_name, password_hash FROM users WHERE cpf = :cpf LIMIT 1";

$stmt = $db->prepare($query);

$cpf = htmlspecialchars(strip_tags($data->cpf));
$stmt->bindParam(':cpf', $cpf);

$stmt->execute();
$num = $stmt->rowCount();

if ($num > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $full_name = $row['full_name'];
    $password_hash = $row['password_hash'];

    if (password_verify($data->password, $password_hash)) {
        // Se a senha estiver correta, criamos as variáveis de sessão
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['logged_in'] = true;

        http_response_code(200);
        echo json_encode(array(
            "message" => "Login bem-sucedido.",
            "user" => array(
                "id" => $id,
                "name" => $full_name
            )
        ));
    } else {
        // Senha incorreta
        http_response_code(401);
        echo json_encode(array("message" => "CPF ou senha inválidos."));
    }
} else {
    // Usuário não encontrado
    http_response_code(401);
    echo json_encode(array("message" => "CPF ou senha inválidos."));
}
