<?php
require_once __DIR__ . '/smtp-mailer.php';

function clean_input($value): string {
    return trim(strip_tags((string) ($value ?? '')));
}

function config_preview(array $smtp): array {
    return [
        'host' => $smtp['host'] ?? null,
        'port' => $smtp['port'] ?? null,
        'encryption' => $smtp['encryption'] ?? null,
        'from_email' => $smtp['from_email'] ?? null,
        'from_name' => $smtp['from_name'] ?? null,
        'username_hint' => isset($smtp['username']) ? substr((string) $smtp['username'], 0, 3) . '***' : null,
    ];
}

$defaultTo = 'contato@rbbautomacao.com.br';
$defaultReplyTo = 'contato@rbbautomacao.com.br';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Teste SMTP</title>
</head>
<body>
  <h1>Teste SMTP (temporario)</h1>
  <p>Este endpoint usa <code>smtp-mailer.php</code> e <code>smtp-config.local.php</code> (fora do Git).</p>
  <form method="post">
    <label>Destino <input type="email" name="to" value="<?php echo htmlspecialchars($defaultTo, ENT_QUOTES, 'UTF-8'); ?>" required></label><br><br>
    <label>Reply-To <input type="email" name="reply_to" value="<?php echo htmlspecialchars($defaultReplyTo, ENT_QUOTES, 'UTF-8'); ?>" required></label><br><br>
    <label>Assunto <input type="text" name="subject" value="Teste SMTP real - RBB Automacao" required></label><br><br>
    <label>Mensagem<br><textarea name="message" rows="8" cols="60" required>Teste SMTP executado por teste-smtp.php.</textarea></label><br><br>
    <button type="submit">Enviar teste</button>
  </form>
</body>
</html>
    <?php
    exit;
}

$to = clean_input($_POST['to'] ?? $defaultTo);
$replyTo = clean_input($_POST['reply_to'] ?? $defaultReplyTo);
$subject = clean_input($_POST['subject'] ?? 'Teste SMTP real - RBB Automacao');
$message = trim((string) ($_POST['message'] ?? 'Teste SMTP executado por teste-smtp.php.'));

if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($replyTo, FILTER_VALIDATE_EMAIL) || $subject === '' || $message === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'error' => 'Parametros invalidos',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$sendError = null;
$success = smtp_send_mail($to, $subject, $message, $replyTo, $sendError);
$smtp = smtp_load_config();

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'success' => $success,
    'timestamp' => gmdate('c'),
    'to' => $to,
    'reply_to' => $replyTo,
    'error' => $sendError,
    'config_preview' => config_preview($smtp),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
