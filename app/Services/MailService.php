<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Send a password reset OTP email using PHPMailer.
     *
     * Returns true on success, false on failure (never throws — callers
     * must treat a false return as a graceful degradation).
     */
    public function sendOtpEmail(string $toEmail, string $toName, string $otp): bool
    {
        $mail = new PHPMailer(true);

        try {
            // ── Transport ──────────────────────────────────────────────
            $mailerDriver = config('mail.default', 'log');

            if ($mailerDriver === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = config('mail.mailers.smtp.host', '127.0.0.1');
                $mail->Port       = (int) config('mail.mailers.smtp.port', 587);
                $mail->Username   = config('mail.mailers.smtp.username', '');
                $mail->Password   = config('mail.mailers.smtp.password', '');

                $scheme = config('mail.mailers.smtp.scheme', '');
                if ($scheme === 'ssl' || (int) config('mail.mailers.smtp.port') === 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($scheme === 'tls' || (int) config('mail.mailers.smtp.port') === 587) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } else {
                    $mail->SMTPAutoTLS = false;
                    $mail->SMTPSecure  = '';
                }

                if (!empty($mail->Username)) {
                    $mail->SMTPAuth = true;
                }

                $mail->Timeout = 15;
                $mail->SMTPDebug = SMTP::DEBUG_OFF;
            } else {
                // Fallback: use PHP sendmail / log mailer
                $mail->isMail();
            }

            // ── Sender & Recipient ─────────────────────────────────────
            $fromAddress = config('mail.from.address', 'no-reply@discusshub.local');
            $fromName    = config('mail.from.name', config('app.name', 'DiscussHub'));

            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo($fromAddress, $fromName);

            // ── Content ────────────────────────────────────────────────
            $mail->isHTML(true);
            $mail->CharSet  = PHPMailer::CHARSET_UTF8;
            $mail->Subject  = 'Your DiscussHub Password Reset Code — ' . $otp;
            $mail->Body     = $this->buildOtpHtml($toName, $otp);
            $mail->AltBody  = $this->buildOtpText($toName, $otp);

            $mail->send();

            Log::info('[MailService] OTP email sent successfully.', [
                'to' => $toEmail,
            ]);

            return true;

        } catch (PHPMailerException $e) {
            Log::error('[MailService] PHPMailer failed to send OTP email.', [
                'to'    => $toEmail,
                'error' => $e->getMessage(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('[MailService] Unexpected error sending OTP email.', [
                'to'    => $toEmail,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    //  Email Templates
    // ──────────────────────────────────────────────────────────────────

    private function buildOtpHtml(string $name, string $otp): string
    {
        $appName  = config('app.name', 'DiscussHub');
        $appUrl   = rtrim(config('app.url', 'http://localhost'), '/');
        $year     = date('Y');

        $digits = '';
        foreach (str_split($otp) as $d) {
            $digits .= '<span style="display:inline-block;width:44px;height:52px;line-height:52px;text-align:center;
                         background:#f59e0b;color:#ffffff;font-size:24px;font-weight:800;border-radius:10px;
                         margin:0 4px;letter-spacing:0;font-family:monospace;">' . htmlspecialchars($d) . '</span>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Password Reset OTP</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 0;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;">

        <!-- Header -->
        <tr>
          <td align="center" style="padding-bottom:24px;">
            <span style="font-size:22px;font-weight:800;color:#1e293b;letter-spacing:-0.5px;">
              💬 {$appName}
            </span>
          </td>
        </tr>

        <!-- Card -->
        <tr>
          <td style="background:#ffffff;border-radius:16px;padding:40px 40px 32px;
                     box-shadow:0 4px 24px rgba(0,0,0,0.08);border:1px solid #e2e8f0;">

            <!-- Icon -->
            <div style="text-align:center;margin-bottom:24px;">
              <div style="display:inline-block;width:64px;height:64px;border-radius:50%;
                          background:linear-gradient(135deg,#fef3c7,#fde68a);
                          line-height:64px;font-size:28px;">🔐</div>
            </div>

            <!-- Title -->
            <h1 style="margin:0 0 8px;font-size:22px;font-weight:800;color:#0f172a;text-align:center;">
              Password Reset Request
            </h1>
            <p style="margin:0 0 28px;color:#64748b;font-size:14px;text-align:center;line-height:1.6;">
              Hi <strong style="color:#1e293b;">{$name}</strong>, use the verification code below
              to reset your password. It expires in <strong>10 minutes</strong>.
            </p>

            <!-- OTP Block -->
            <div style="text-align:center;margin-bottom:28px;padding:24px;
                        background:#fffbeb;border-radius:12px;border:2px dashed #fcd34d;">
              <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:0.1em;
                         color:#92400e;text-transform:uppercase;">Your Verification Code</p>
              <div style="margin-bottom:8px;">
                {$digits}
              </div>
              <p style="margin:12px 0 0;font-size:11px;color:#b45309;">
                ⏱ Expires in 10 minutes &nbsp;·&nbsp; Do not share this code with anyone
              </p>
            </div>

            <!-- Security Note -->
            <div style="background:#f8fafc;border-radius:10px;padding:16px;border-left:3px solid #f59e0b;margin-bottom:24px;">
              <p style="margin:0;font-size:13px;color:#475569;line-height:1.6;">
                <strong style="color:#1e293b;">Didn't request this?</strong> You can safely ignore this email.
                Your account remains secure and no changes have been made.
              </p>
            </div>

            <!-- Divider -->
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 20px;">

            <p style="margin:0;font-size:12px;color:#94a3b8;text-align:center;line-height:1.6;">
              This email was sent to <strong>{$name}</strong> from {$appName}.<br>
              If you need help, visit <a href="{$appUrl}/contact" style="color:#f59e0b;text-decoration:none;">{$appUrl}/contact</a>
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:20px 0;text-align:center;">
            <p style="margin:0;font-size:11px;color:#94a3b8;">
              &copy; {$year} {$appName} &nbsp;·&nbsp;
              <a href="{$appUrl}" style="color:#94a3b8;text-decoration:none;">Visit Site</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }

    private function buildOtpText(string $name, string $otp): string
    {
        $appName = config('app.name', 'DiscussHub');
        return <<<TEXT
Hi {$name},

Your {$appName} password reset verification code is:

  {$otp}

This code expires in 10 minutes. Do not share it with anyone.

If you did not request a password reset, you can safely ignore this email.

— The {$appName} Team
TEXT;
    }
}
