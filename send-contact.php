<?php
$destinationEmail = 'lenadrosantos57.ls@gmail.com';

function clean_input($value) {
    return trim(strip_tags($value ?? ''));
}

function redirect_with_status($status) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
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
$segmento = clean_input($_POST['segmento'] ?? '');
$sla = clean_input($_POST['sla'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$website = clean_input($_POST['website'] ?? '');
$subject = clean_input($_POST['_subject'] ?? 'Contato pelo site - RBB Automacao');

if ($website !== '') {
    redirect_with_status('ok');
}

if (
    !$nome || !$empresa || !$telefone || !$email || !$servico || !$mensagem ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($mensagem) > 5000
) {
    redirect_with_status('erro');
}

$body = "Novo contato recebido pelo site da RBB Automacao\n\n" .
        "Nome: {$nome}\n" .
        "Empresa: {$empresa}\n" .
        "Telefone: {$telefone}\n" .
        "E-mail: {$email}\n" .
        "Servico de interesse: {$servico}\n";

if ($segmento !== '') {
    $body .= "Segmento industrial: {$segmento}\n";
}

if ($sla !== '') {
    $body .= "SLA desejado: {$sla}\n";
}

$body .= "\nMensagem:\n{$mensagem}\n";

$headers = "From: RBB Automacao <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\n";
$headers .= "Reply-To: {$email}\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\n";

$sent = @mail($destinationEmail, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
redirect_with_status($sent ? 'ok' : 'erro');
?>