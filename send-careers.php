<?php
require_once __DIR__ . '/smtp-mailer.php';

$destinationEmail = 'contato@rbbautomacao.com.br';

function clean_input($value) {
    return trim(strip_tags($value ?? ''));
}

function redirect_with_status($status) {
    header('Location: obrigado.html?status=' . $status);
    exit;
}

function handle_send_error(string $sendError): void {
    $requestId = bin2hex(random_bytes(6));
    error_log("SMTP careers send failed [{$requestId}]: {$sendError}");
    http_response_code(502);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Não foi possível enviar seu cadastro agora. Tente novamente em alguns minutos.\n";
    echo "Código de diagnóstico: {$requestId}\n";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_status('erro');
}

$nome = clean_input($_POST['nome'] ?? '');
$telefone = clean_input($_POST['telefone'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$area = clean_input($_POST['area'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$subject = clean_input($_POST['_subject'] ?? 'Trabalhe Conosco - RBB Automação');

if (!$nome || !$telefone || !$email || !$area || !$mensagem || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('erro');
}

$body = "Novo cadastro recebido pela página Trabalhe Conosco da RBB Automação\r\n\r\n" .
        "Nome: {$nome}\r\n" .
        "Telefone: {$telefone}\r\n" .
        "E-mail: {$email}\r\n" .
        "Área de interesse: {$area}\r\n\r\n" .
        "Resumo profissional:\r\n{$mensagem}\r\n";

$sendError = null;
$sent = smtp_send_mail($destinationEmail, $subject, $body, $email, $sendError);

if (!$sent) {
    handle_send_error($sendError ?? 'Unknown SMTP error');
}

redirect_with_status('ok');
