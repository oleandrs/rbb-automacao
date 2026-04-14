<?php
// Endpoint temporario para validar o envio de email via mail() no servidor.

declare(strict_types=1);

$to = 'contato@rbbautomacao.com.br';
$subject = 'Teste de envio - formulario RBB';
$message = "Teste automatico de envio via mail() em " . date('Y-m-d H:i:s') . ".";
$headers = [
    'From: contato@rbbautomacao.com.br',
    'Reply-To: contato@rbbautomacao.com.br',
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8',
];

$sent = mail($to, $subject, $message, implode("\r\n", $headers));

header('Content-Type: application/json; charset=UTF-8');
http_response_code($sent ? 200 : 500);

echo json_encode([
    'success' => $sent,
    'to' => $to,
    'timestamp' => date(DATE_ATOM),
    'message' => $sent
        ? 'mail() executado com sucesso. Verifique a caixa de entrada do destinatario.'
        : 'mail() falhou. Verifique logs do servidor/hospedagem.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
