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
?>
<?php
$nome = clean_input($_POST['nome'] ?? '');
$telefone = clean_input($_POST['telefone'] ?? '');
$email = clean_input($_POST['email'] ?? '');
$area = clean_input($_POST['area'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$website = clean_input($_POST['website'] ?? '');
$subject = clean_input($_POST['_subject'] ?? 'Trabalhe Conosco - RBB Automação');

if ($website !== '') {
    redirect_with_status('ok');
}

if (
    !$nome || !$telefone || !$email || !$area || !$mensagem ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($mensagem) > 5000
) {
    redirect_with_status('erro');
}

$body = "Novo cadastro recebido pela página Trabalhe Conosco da RBB Automação

" .
        "Nome: {$nome}
" .
        "Telefone: {$telefone}
" .
        "E-mail: {$email}
" .
        "Área de interesse: {$area}

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
