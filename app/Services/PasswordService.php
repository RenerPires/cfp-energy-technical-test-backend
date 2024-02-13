<?php

namespace App\Services;

use App\Models\PasswordResetTokens;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Mockery\Generator\StringManipulation\Pass\Pass;
use Resend\Laravel\Facades\Resend;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PasswordService
{
    protected static function resetPasswordEmailTemplate($email, $resetPasswordLink): HtmlString
    {
        $template = <<<HTML
        <html dir="ltr" lang="en">
          <body style="font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;font-size:1.0769230769230769em;min-height:100%;line-height:155%">
            <table align="left" width="100%" border="0" cellPadding="0" cellSpacing="0" role="presentation" style="align:left;width:auto;padding-left:0;padding-right:0;max-width:600px;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif">
              <tbody>
                <tr>
                  <td>
                    <h2 style="margin:0;font-size:2.25em;line-height:1.44em;padding: 0.389em 0 0;font-weight:600;text-align:left"><span>Reset your password</span></h2>
                    <p style="margin:0;font-size:1em;padding: 0.5em 0;text-align:left"></p>
                    <p style="margin:0;font-size:1em;padding: 0.5em 0;text-align:left"><span>We've received a request to reset the password for the User Management App account associated with </span><span><a href="mailto:$email" rel="noopener noreferrer nofollow" style="color:#0670DB;text-decoration:underline" target="_blank">$email</a></span><span>. No changes have been made to your account yet. To reset your password, click on the button below.</span></p>
                    <p style="margin:0;font-size:1em;padding: 0.5em 0;text-align:left"></p>
                    <table align="center" width="100%" border="0" cellPadding="0" cellSpacing="0" role="presentation">
                      <tbody style="width:100%">
                        <tr style="width:100%">
                          <td align="left" data-id="__react-email-column"><a href="$resetPasswordLink" style="margin:0;display:inline-block;background:#000000;padding: 0.5em 0.8em;border-radius:4px;color:#ffffff;border-style:solid;width:auto;border-color:#000000;border-width:1px;line-height:100%;text-decoration:none;max-width:100%" target="_blank"><span></span><span style="max-width:100%;display:inline-block;line-height:120%;mso-padding-alt:0;mso-text-raise:6px"><span>Reset your password</span></span><span></span></a></td>
                        </tr>
                      </tbody>
                    </table>
                    <p style="margin:0;font-size:1em;padding: 0.5em 0;text-align:left"></p>
                    <p style="margin:0;font-size:1em;padding: 0.5em 0;text-align:left"><span>If you didn't request for a password reset, you can safely ignore this email.</span></p>
                    <p style="margin:0;font-size:1em;padding: 0.5em 0;text-align:left"></p>
                    <p style="margin:0;font-size:1em;padding: 0.5em 0;text-align:left"></p>
                  </td>
                </tr>
              </tbody>
            </table>
          </body>

        </html>
HTML;
        return new HtmlString($template);
    }
    public static function changePassword($user, $payload): void
    {
        $oldPassword = $payload["password"];
        $newPassword = $payload["new_password"];

        if(!Hash::check($oldPassword, $user->password)) {
            throw new BadRequestHttpException("old password dont match", code: Response::HTTP_BAD_REQUEST);
        }

        $user->update(["password" => Hash::make($newPassword)]);
    }
    public static function forgotPassword($payload): string
    {
        $email = $payload['email'];
        $user = User::where('email', $email)->first();

        if($user) {
            $token = Str::random();

            PasswordResetTokens::updateOrInsert([
                'email' => $email
            ], [
                'token' => $token,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(10)
            ]);

            Resend::emails()->send([
                'from' => 'User Management App <no-reply@renerpires.dev>',
                'to' => $email,
                'subject' => 'Password Recovery',
                'html' => (self::resetPasswordEmailTemplate($email, Env('FRONTEND_APP_ADDRESS', 'localhost')."/reset-password?password-reset-token=$token"))->toHtml(),
            ]);
        }

        return $token ?? "";
    }
    public static function resetPassword($token, $payload): string
    {
        $newPassword = $payload["password"];

        $validateToken = PasswordResetTokens::where([
                ['token', '=', $token],
                ['expires_at', '>', now()]
            ])
            ->first();

        if(!$validateToken) {
            throw new BadRequestHttpException("invalid or expired token", code: Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $validateToken->email)->first();

        $user->update(["password" => Hash::make($newPassword)]);

        $token = auth()->attempt(['email' => $user->email, 'password' => $newPassword]);

        Password::deleteToken($user);

        return $token;
    }
}
