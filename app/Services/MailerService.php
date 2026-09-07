<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailerService
{
    public static function send(string $to, string $subject, string $html): bool
    {
        $host = (string) env('MAIL_HOST', '');

        if ($host === '') {
            self::logToFile($to, $subject, $html);

            return true;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = (int) env('MAIL_PORT', 587);
            $mail->SMTPAuth = true;
            $mail->Username = (string) env('MAIL_USERNAME', '');
            $mail->Password = (string) env('MAIL_PASSWORD', '');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom((string) env('MAIL_FROM_ADDRESS', 'noreply@prisma.app'), (string) env('MAIL_FROM_NAME', 'PRISMA'));
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;

            $mail->send();

            return true;
        } catch (PHPMailerException $e) {
            error_log('[MailerService] Falha ao enviar e-mail: ' . $mail->ErrorInfo);

            return false;
        }
    }

    public static function sendWelcome(array $user): bool
    {
        $html = '<p>Olá, ' . e($user['name']) . '!</p>'
            . '<p>Sua conta na PRISMA foi criada com sucesso.</p>';

        return self::send($user['email'], 'Bem-vindo à PRISMA', $html);
    }

    public static function sendPasswordReset(string $email, string $token): bool
    {
        $link = url('/reset/' . $token);
        $html = '<p>Você solicitou a redefinição de senha.</p>'
            . '<p><a href="' . e($link) . '">Clique aqui para redefinir sua senha</a></p>'
            . '<p>Este link expira em 1 hora. Se você não solicitou, ignore este e-mail.</p>';

        return self::send($email, 'Redefinição de senha — PRISMA', $html);
    }

    private static function logToFile(string $to, string $subject, string $html): void
    {
        $line = sprintf(
            "[%s] Para: %s | Assunto: %s\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $html,
            str_repeat('-', 60)
        );

        file_put_contents(ROOT . '/storage/logs/mail.log', $line, FILE_APPEND);
    }
}
