<?php

namespace App\Contracts;

interface EmailNotificationServiceInterface
{
    /**
     * Send an email notification.
     *
     * @param string $subject
     * @param string $email_message
     * @param string $email
     * @return void|string
     */
    public function sendEmail(string $subject = "", string $email_message = "", string $email = "");

    /**
     * Send a forgot password email.
     *
     * @param string $email
     * @param string $token
     * @return void|string
     */
    public function sendForgotPasswordEmail(string $email, string $token);
}
