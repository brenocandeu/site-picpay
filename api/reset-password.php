<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config/database.php';

$data = json_decode(file_get_contents("php://input"));

// Medida de segurança: verifica a autorização da sessão
if (empty($_SESSION['reset_authorized_for_cpf']) || $_SESSION['reset_authorized_for_cpf'] !== $data->cpf) {
    http_response_code(401);
    echo json_encode(array("message" => "Não autorizado. Por favor, complete o processo de verificação novamente."));
    exit();
}

// Validação dos dados recebidos
if (empty($data->cpf) || empty($data->password)) { /* ... */
}
if (strlen($data->password) < 8) { /* ... */
}

$database = new Database();
$db = $database->getConnection();

// --- INÍCIO DA NOVA VERIFICAÇÃO ---

// 1. Busca a senha atual (hash) do usuário no banco
$query_get_pass = "SELECT password_hash FROM users WHERE cpf = :cpf LIMIT 1";
$stmt_get_pass = $db->prepare($query_get_pass);
$cpf = htmlspecialchars(strip_tags($data->cpf));
$stmt_get_pass->bindParam(':cpf', $cpf);
$stmt_get_pass->execute();

if ($stmt_get_pass->rowCount() > 0) {
    $row = $stmt_get_pass->fetch(PDO::FETCH_ASSOC);
    $current_password_hash = $row['password_hash'];

    // 2. Compara a nova senha enviada com a senha atual (hash)
    if (password_verify($data->password, $current_password_hash)) {
        // Se forem iguais, retorna um erro para o usuário
        http_response_code(400); // Bad Request
        echo json_encode(array("message" => "A nova senha não pode ser igual à senha anterior. Por favor, escolha uma diferente."));
        exit();
    }
}
// --- FIM DA NOVA VERIFICAÇÃO ---


// Se a nova senha for diferente, o script continua para atualizá-la...
$password_hash = password_hash($data->password, PASSWORD_BCRYPT);
$query_update_pass = "UPDATE users SET password_hash = :password_hash WHERE cpf = :cpf";
$stmt_update_pass = $db->prepare($query_update_pass);
$stmt_update_pass->bindParam(':password_hash', $password_hash);
$stmt_update_pass->bindParam(':cpf', $cpf);

if ($stmt_update_pass->execute()) {
    unset($_SESSION['reset_authorized_for_cpf']);
    http_response_code(200);
    echo json_encode(array("message" => "Senha redefinida com sucesso!"));
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Não foi possível redefinir a senha."));
}
