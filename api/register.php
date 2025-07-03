<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"));

// Validação dos dados de entrada
if (empty($data->cpf) || empty($data->fullName) || empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Dados incompletos."));
    exit();
}

// Gera o código de verificação
$verification_code = rand(100000, 999999);

// **A MÁGICA ACONTECE AQUI**
// Armazena os dados do formulário e o código na sessão, em vez de no banco
$_SESSION['pending_registration_data'] = $data;
$_SESSION['pending_verification_code'] = $verification_code;

$mail = new PHPMailer(true);

try {
    // Configurações do Servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username = 'email@email.com'; // SEU E-MAIL
    $mail->Password = 'senha'; // SUA SENHA DE APP
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    // Remetente e Destinatário
    $mail->setFrom('email@email.com', 'Picpay');
    $mail->addAddress($data->email, $data->fullName);

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Seu Código de Ativação - Picpay';
    $mail->Body = "
        <div style='font-family: Poppins, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden;'>
            <div style='background-color: #097c49; padding: 20px; text-align: center;'>
                <img src='https://placehold.co/150x50/097c49/FFFFFF?text=Picpay' alt='Picpay Logo' style='display: inline-block;'>
            </div>
            <div style='padding: 30px 25px; color: #333;'>
                <h2 style='color: #0d0d0d; font-size: 24px;'>Bem-vindo, {$data->fullName}!</h2>
                <p style='font-size: 16px; color: #555; line-height: 1.6;'>Seu cadastro no Picpay está quase pronto! Para garantir a sua segurança e ativar sua conta, utilize o código de verificação abaixo.</p>
                <div style='background-color: #f4f7f9; border-radius: 8px; text-align: center; padding: 20px; margin: 25px 0;'>
                    <p style='font-size: 16px; margin: 0; color: #555;'>Seu código de ativação é:</p>
                    <p style='font-size: 36px; font-weight: 700; color: #097c49; letter-spacing: 5px; margin: 10px 0;'>{$verification_code}</p>
                </div>
                <p style='font-size: 14px; color: #555;'>Se você não criou uma conta no Picpay, por favor, apenas ignore este e-mail.</p>
            </div>
            <div style='background-color: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #888;'>
                © " . date('Y') . " Picpay. Todos os direitos reservados.
            </div>
        </div>
    ";

    $mail->send();

    http_response_code(200);
    echo json_encode(array("message" => "Cadastro quase concluído! Verifique seu e-mail para o código de ativação."));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Não foi possível enviar o e-mail de verificação. Erro: {$mail->ErrorInfo}"));
}
