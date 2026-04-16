<?php

function smtp_env(string $key, $default = null) {
    $value = getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }
    return $value;
}

function smtp_load_config(): array {
    $config = [];
    $configPath = smtp_env('SMTP_CONFIG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'smtp-config.local.php');
    if ($configPath && is_file($configPath)) {
        $fileConfig = require $configPath;
        if (is_array($fileConfig)) {
            $config = $fileConfig;
        }
    }

    $merged = [
        'host' => smtp_env('SMTP_HOST', $config['host'] ?? null),
        'port' => (int) smtp_env('SMTP_PORT', $config['port'] ?? 587),
        'username' => smtp_env('SMTP_USERNAME', $config['username'] ?? null),
        'password' => smtp_env('SMTP_PASSWORD', $config['password'] ?? null),
        'encryption' => strtolower((string) smtp_env('SMTP_ENCRYPTION', $config['encryption'] ?? 'tls')),
        'from_email' => smtp_env('SMTP_FROM_EMAIL', $config['from_email'] ?? null),
        'from_name' => smtp_env('SMTP_FROM_NAME', $config['from_name'] ?? 'RBB Automação'),
        'timeout' => (int) smtp_env('SMTP_TIMEOUT', $config['timeout'] ?? 15),
    ];

    if ($merged['from_email'] === null || $merged['from_email'] === '') {
        $merged['from_email'] = $merged['username'];
    }

    return $merged;
}

function smtp_send_command($socket, string $command, array $expectedCodes): void {
    fwrite($socket, $command . "\r\n");
    $response = smtp_read_response($socket);
    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('SMTP command failed: ' . trim($response));
    }
}

function smtp_read_response($socket): string {
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    if ($response === '') {
        throw new RuntimeException('Empty SMTP response');
    }
    return $response;
}

function smtp_connect(array $smtp) {
    $host = (string) ($smtp['host'] ?? '');
    $port = (int) ($smtp['port'] ?? 0);
    $encryption = (string) ($smtp['encryption'] ?? 'tls');
    $timeout = (int) ($smtp['timeout'] ?? 15);

    if ($host === '' || $port <= 0) {
        throw new RuntimeException('SMTP host/port not configured');
    }

    $target = $encryption === 'ssl' ? "ssl://{$host}:{$port}" : "{$host}:{$port}";
    $socket = @stream_socket_client($target, $errno, $errstr, $timeout);
    if (!$socket) {
        throw new RuntimeException("SMTP connection failed: {$errstr} ({$errno})");
    }

    stream_set_timeout($socket, $timeout);

    $greeting = smtp_read_response($socket);
    $code = (int) substr($greeting, 0, 3);
    if ($code !== 220) {
        fclose($socket);
        throw new RuntimeException('Invalid SMTP greeting: ' . trim($greeting));
    }

    return $socket;
}

function smtp_format_address(string $email, string $name = ''): string {
    $safeName = trim(preg_replace('/[\r\n]+/', ' ', $name));
    if ($safeName === '') {
        return "<{$email}>";
    }
    $encoded = '=?UTF-8?B?' . base64_encode($safeName) . '?=';
    return "{$encoded} <{$email}>";
}

function smtp_send_mail(string $toEmail, string $subject, string $bodyText, string $replyToEmail, ?string &$error = null): bool {
    try {
        $smtp = smtp_load_config();
        if (!$smtp['host'] || !$smtp['username'] || !$smtp['password'] || !$smtp['from_email']) {
            throw new RuntimeException('Missing SMTP config (host/username/password/from_email)');
        }

        $socket = smtp_connect($smtp);
        $clientHost = $_SERVER['SERVER_NAME'] ?? 'localhost';

        smtp_send_command($socket, "EHLO {$clientHost}", [250]);

        if ($smtp['encryption'] === 'tls') {
            smtp_send_command($socket, 'STARTTLS', [220]);
            $cryptoEnabled = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($cryptoEnabled !== true) {
                throw new RuntimeException('Failed to enable TLS encryption');
            }
            smtp_send_command($socket, "EHLO {$clientHost}", [250]);
        }

        smtp_send_command($socket, 'AUTH LOGIN', [334]);
        smtp_send_command($socket, base64_encode((string) $smtp['username']), [334]);
        smtp_send_command($socket, base64_encode((string) $smtp['password']), [235]);
        smtp_send_command($socket, 'MAIL FROM:<' . $smtp['from_email'] . '>', [250]);
        smtp_send_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtp_send_command($socket, 'DATA', [354]);

        $safeBody = preg_replace('/\r\n|\r|\n/', "\r\n", $bodyText);
        $safeBody = preg_replace('/^\./m', '..', $safeBody);

        $headers = [];
        $headers[] = 'Date: ' . date(DATE_RFC2822);
        $headers[] = 'From: ' . smtp_format_address((string) $smtp['from_email'], (string) $smtp['from_name']);
        $headers[] = 'To: <' . $toEmail . '>';
        $headers[] = 'Reply-To: <' . $replyToEmail . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $safeBody . "\r\n.";
        fwrite($socket, $message . "\r\n");

        $result = smtp_read_response($socket);
        $resultCode = (int) substr($result, 0, 3);
        if ($resultCode !== 250) {
            throw new RuntimeException('SMTP DATA failed: ' . trim($result));
        }

        smtp_send_command($socket, 'QUIT', [221]);
        fclose($socket);
        return true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
        return false;
    }
}
