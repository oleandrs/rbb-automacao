<?php
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
?>
<?php
$nome = clean_input($_POST['nome'] ?? '');
$empresa = clean_input($_POST['empresa'] ?? '');
$telefone = clean_input($_POST['telefone'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$servico = clean_input($_POST['servico'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$subject = clean_input($_POST['_subject'] ?? 'Contato pelo site - RBB Automação');

if (!$nome || !$empresa || !$telefone || !$email || !$servico || !$mensagem || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_status('erro');
}

$body = "Novo contato recebido pelo site da RBB Automação

" .
        "Nome: {$nome}
" .
        "Empresa: {$empresa}
" .
        "Telefone: {$telefone}
" .
        "E-mail: {$email}
" .
        "Serviço de interesse: {$servico}

" .
        "Mensagem:
{$mensagem}
";

$headers = "From: RBB Automação <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">
";
$headers .= "Reply-To: {$email}
";
$headers .= "Content-Type: text/plain; charset=UTF-8
";

$sent = @mail($destinationEmail, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
redirect_with_status($sent ? 'ok' : 'erro');
?>