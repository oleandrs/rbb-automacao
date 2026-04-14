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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_status('erro');
}

$nome = clean_input($_POST['nome'] ?? '');
$empresa = clean_input($_POST['empresa'] ?? '');
$telefone = clean_input($_POST['telefone'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$servico = clean_input($_POST['servico'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$subject = clean_input($_POST['_subject'] ?? 'Contato pelo site - RBB Automacao');

if (!$nome || !$empresa || !$telefone || !$email || !$servico || !$mensagem || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('erro');
}

$body = "Novo contato recebido pelo site da RBB Automacao\r\n\r\n" .
        "Nome: {$nome}\r\n" .
        "Empresa: {$empresa}\r\n" .
        "Telefone: {$telefone}\r\n" .
        "E-mail: {$email}\r\n" .
        "Servico de interesse: {$servico}\r\n\r\n" .
        "Mensagem:\r\n{$mensagem}\r\n";

$sendError = null;
$sent = smtp_send_mail($destinationEmail, $subject, $body, $email, $sendError);

if (!$sent) {
    error_log('SMTP contact send failed: ' . $sendError);
}

redirect_with_status($sent ? 'ok' : 'erro');

