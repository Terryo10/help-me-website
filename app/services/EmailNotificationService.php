<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    public function sendEmail($subject = "", $email_message = "", $email = "")
    {
        try {
            Mail::send('emails.notifications', ['email_message' => $email_message, 'subject' => $subject], function ($message) use ($email, $subject) {
                $message->to($email);
                $message->subject($subject);
            });
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
