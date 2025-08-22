<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->subject('Verify Your Email')->markdown('emails.verify_email', ['user' => $notifiable,                'verificationUrl' => $this->verificationUrl($notifiable),]);
    }


    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $notifiable->getKey()]);
    }
}
