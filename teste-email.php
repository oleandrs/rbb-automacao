<?php
// Endpoint temporario para validar envio via mail() e expor diagnostico basico da hospedagem.

declare(strict_types=1);

$to = 'contato@rbbautomacao.com.br';
$subject = 'Teste de envio - formulario RBB';
$timestamp = date(DATE_ATOM);
$message = "Teste automatico de envio via mail() em {$timestamp}.";
$headers = [
    'From: contato@rbbautomacao.com.br',
    'Reply-To: contato@rbbautomacao.com.br',
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8',
];

$validation = [
    'toIsValid' => filter_var($to, FILTER_VALIDATE_EMAIL) !== false,
    'fromDomain' => 'rbbautomacao.com.br',
];

$sent = false;
$errorBefore = error_get_last();
$warning = null;

set_error_handler(static function (int $errno, string $errstr) use (&$warning): bool {
    $warning = [
        'code' => $errno,
        'message' => $errstr,
    ];

    // Evita output HTML de warning e permite retorno JSON limpo.
    return true;
});

try {
    // Usa envelope sender para aumentar chance de aceite em alguns provedores.
    $sent = mail($to, $subject, $message, implode("\r\n", $headers), '-f contato@rbbautomacao.com.br');
} finally {
    restore_error_handler();
}

$errorAfter = error_get_last();

header('Content-Type: application/json; charset=UTF-8');
http_response_code($sent ? 200 : 500);

echo json_encode([
    'success' => $sent,
    'to' => $to,
    'timestamp' => $timestamp,
    'validation' => $validation,
    'php' => [
        'version' => phpversion(),
        'sapi' => PHP_SAPI,
        'sendmail_path' => ini_get('sendmail_path') ?: null,
        'SMTP' => ini_get('SMTP') ?: null,
        'smtp_port' => ini_get('smtp_port') ?: null,
    ],
    'mailWarning' => $warning,
    'errorBefore' => $errorBefore,
    'errorAfter' => $errorAfter,
    'message' => $sent
        ? 'mail() retornou true. Se o email nao chegar, validar spam/relay/log do provedor.'
        : 'mail() retornou false. Verifique bloqueio no provedor, sendmail_path/SMTP e logs da hospedagem.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
