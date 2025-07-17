<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    public function __construct()
    {
        // You can initialize any properties or dependencies here if needed
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
