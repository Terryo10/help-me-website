<?php

namespace App\Services;

use App\Contracts\EmailNotificationServiceInterface;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService implements EmailNotificationServiceInterface
{
    public function __construct($subject = "", $email_message = "", $email = "", $type = "send")
    {
        if ($type == "send") {
            $this->sendEmail($subject, $email_message, $email);
        } elseif ($type == "forgot_password") {
            $this->sendForgotPasswordEmail($email, $subject);
        }
    }
    /**
     * Send an email notification.
     *
     * @param string $subject
     * @param string $email_message
     * @param string $email
     * @return void|string
     */

    public function sendEmail($subject = "", $email_message = "", $email = "")
    {
        try {
            Mail::send('emails.notifications', ['email_message' => $email_message, 'subject' => $subject], function ($message) use ($email, $subject) {
                $message->to($email);
                $message->subject($subject);
            });

            return "Email sent successfully to {$email}";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function sendForgotPasswordEmail($email, $token)
    {
        try {
            Mail::send('emails.forgot-password', ['token' => $token, 'email' => $email], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Reset Password');
            });
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
