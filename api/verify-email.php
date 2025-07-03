<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config/database.php';

$data = json_decode(file_get_contents("php://input"));

// Valida se os dados necessários (da sessão e do POST) existem
if (
    empty($data->code) ||
    empty($_SESSION['pending_verification_code']) ||
    empty($_SESSION['pending_registration_data'])
) {
    http_response_code(400);
    echo json_encode(array("message" => "Sessão inválida ou dados incompletos. Por favor, inicie o cadastro novamente."));
    exit();
}

// Compara o código enviado pelo usuário com o código guardado na sessão
if ((int)$data->code === (int)$_SESSION['pending_verification_code']) {

    // Código correto! Pega os dados da sessão para salvar no banco.
    $userData = $_SESSION['pending_registration_data'];

    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO users (cpf, full_name, birth_date, phone, email, password_hash) VALUES (:cpf, :full_name, :birth_date, :phone, :email, :password_hash)";
    $stmt = $db->prepare($query);

    // Limpa e associa os parâmetros
    $cpf = htmlspecialchars(strip_tags($userData->cpf));
    $fullName = htmlspecialchars(strip_tags($userData->fullName));
    $birthDate = htmlspecialchars(strip_tags($userData->nascimento));
    $phone = htmlspecialchars(strip_tags($userData->celular));
    $email = htmlspecialchars(strip_tags($userData->email));
    $password_hash = password_hash($userData->password, PASSWORD_BCRYPT);

    $stmt->bindParam(":cpf", $cpf);
    $stmt->bindParam(":full_name", $fullName);
    $stmt->bindParam(":birth_date", $birthDate);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password_hash", $password_hash);

    // Executa a query final
    try {
        if ($stmt->execute()) {
            // Limpa a sessão para que os dados não possam ser reutilizados
            unset($_SESSION['pending_registration_data']);
            unset($_SESSION['pending_verification_code']);

            http_response_code(201); // Created
            echo json_encode(array("message" => "E-mail verificado e conta criada com sucesso!"));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível finalizar o cadastro."));
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            http_response_code(409); // Conflict
            echo json_encode(array("message" => "CPF ou E-mail já foi cadastrado enquanto você verificava o código."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Erro no banco de dados: " . $e->getMessage()));
        }
    }
} else {
    // Código incorreto
    http_response_code(400);
    echo json_encode(array("message" => "Código de verificação inválido."));
}
