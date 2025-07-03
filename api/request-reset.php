<?php
// Usando as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega o autoloader do Composer
require '../vendor/autoload.php';

// Inicia a sessão
session_start();

// Inicia o buffer de saída para capturar qualquer "ruído"
ob_start();

// Headers da API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->cpf)) {
    ob_clean(); // Limpa o buffer antes de enviar a resposta
    http_response_code(400);
    echo json_encode(array("message" => "O CPF é obrigatório."));
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, email, full_name FROM users WHERE cpf = :cpf LIMIT 1";
$stmt = $db->prepare($query);
$cpf = htmlspecialchars(strip_tags($data->cpf));
$stmt->bindParam(':cpf', $cpf);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $row['id'];
    $user_email = $row['email'];
    $full_name = $row['full_name'];

    $reset_code = rand(100000, 999999);
    $expires_at = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    $update_query = "UPDATE users SET reset_code = :code, reset_expires_at = :expires WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':code', $reset_code);
    $update_stmt->bindParam(':expires', $expires_at);
    $update_stmt->bindParam(':id', $user_id);

    if ($update_stmt->execute()) {
        $mail = new PHPMailer(true);

        try {
            // Configurações do Servidor
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'email@email.com'; // SEU E-MAIL AQUI
            $mail->Password   = 'senha';    // SUA SENHA DE APP GERADA AQUI
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            // Remetente e Destinatário
            $mail->setFrom('email@email.com', 'Picpay');
            $mail->addAddress($user_email, $full_name);

            // Conteúdo do E-mail em HTML
            $mail->isHTML(true);
            $mail->Subject = 'Seu Código de Recuperação de Senha - Picpay';
            $mail->Body    = "
                <div style='font-family: Poppins, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden;'>
                    <div style='background-color: #097c49; padding: 20px; text-align: center;'>
                        <img src='https://placehold.co/150x50/097c49/FFFFFF?text=Picpay' alt='Picpay Logo' style='display: inline-block;'>
                    </div>
                    <div style='padding: 30px 25px; color: #333;'>
                        <h2 style='color: #0d0d0d; font-size: 24px;'>Olá, {$full_name}!</h2>
                        <p style='font-size: 16px; color: #555; line-height: 1.6;'>Recebemos uma solicitação para redefinir a sua senha. Utilize o código de verificação abaixo para continuar o processo.</p>
                        <div style='background-color: #f4f7f9; border-radius: 8px; text-align: center; padding: 20px; margin: 25px 0;'>
                            <p style='font-size: 16px; margin: 0; color: #555;'>Seu código de uso único é:</p>
                            <p style='font-size: 36px; font-weight: 700; color: #097c49; letter-spacing: 5px; margin: 10px 0;'>{$reset_code}</p>
                        </div>
                        <p style='font-size: 14px; color: #555;'>Este código expira em 15 minutos. Se você não solicitou esta alteração, por favor, ignore este e-mail.</p>
                    </div>
                    <div style='background-color: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #888;'>
                        © " . date('Y') . " Picpay. Todos os direitos reservados.
                    </div>
                </div>
            ";
            $mail->AltBody = "Seu código de recuperação é: {$reset_code}. Ele expira em 15 minutos.";

            $mail->send();

            // Limpa o buffer e envia a resposta de sucesso
            ob_clean();
            http_response_code(200);
            echo json_encode(array(
                "message" => "Código de recuperação enviado com sucesso para o seu e-mail.",
                "masked_email" => substr($user_email, 0, 2) . str_repeat('•', strlen(explode('@', $user_email)[0]) - 2) . '@' . explode('@', $user_email)[1]
            ));
        } catch (Exception $e) {
            ob_clean();
            http_response_code(500);
            echo json_encode(array("message" => "Não foi possível enviar o e-mail. Erro do Mailer: {$mail->ErrorInfo}"));
        }
    } else {
        ob_clean();
        http_response_code(503);
        echo json_encode(array("message" => "Não foi possível gerar o código de recuperação."));
    }
} else {
    ob_clean();
    http_response_code(404);
    echo json_encode(array("message" => "Nenhum usuário encontrado com este CPF."));
}

// Finaliza o buffer sem enviar a saída (já limpamos e enviamos o JSON)
ob_end_flush();
