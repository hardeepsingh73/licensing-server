<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\AbstractPart;

/**
 * Listener to log details of sent emails into the database.
 */
class LogSentEmail
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Mail\Events\MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message;

            // Get recipients safely
            $to = $this->getRecipients($message, $event);

            // Extract email content
            $body = $this->getEmailContent($message);

            // Store the email details
            EmailLog::create([
                'to'      => $to,
                'subject' => $message->getSubject() ?? '(No Subject)',
                'body'    => $body,
                'status'  => 'sent',
            ]);
        } catch (\Throwable $e) {
            // Failsafe: log errors into Laravel log, so emails aren’t blocked by DB issues
            Log::error('Failed logging sent email: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Extract recipients from the message.
     *
     * @param  \Swift_Message|\Symfony\Component\Mime\Email  $message
     * @param  \Illuminate\Mail\Events\MessageSent           $event
     * @return string
     */
    protected function getRecipients($message, MessageSent $event): string
    {
        // For system emails like password reset
        if (isset($event->data['email'])) {
            return $event->data['email'];
        }

        // Symfony Mailer (Laravel 9+)
        if (method_exists($message, 'getTo')) {
            $to = $message->getTo();
            if (is_array($to) && !empty($to)) {
                return implode(',', array_map(
                    fn($address) =>
                    $address instanceof Address ? $address->getAddress() : $address,
                    $to
                ));
            }
        }

        // SwiftMailer fallback
        if (method_exists($message, 'getEnvelope')) {
            $recipients = $message->getEnvelope()->getRecipients();
            if (!empty($recipients)) {
                return implode(',', array_map(fn($recipient) => $recipient->getAddress(), $recipients));
            }
        }

        return 'unknown@example.com';
    }

    /**
     * Extract email body content (HTML preferred, else plain text).
     *
     * @param  \Swift_Message|\Symfony\Component\Mime\Email  $message
     * @return string
     */
    protected function getEmailContent($message): string
    {
        $body = $message->getBody();

        // Symfony Mailer (multipart handling)
        if ($body instanceof AbstractPart && method_exists($body, 'getParts')) {
            foreach ($body->getParts() as $part) {
                if ($part->getMediaType() === 'text' && $part->getMediaSubtype() === 'html') {
                    return (string) $part->getBody();
                }
                if ($part->getMediaType() === 'text' && $part->getMediaSubtype() === 'plain') {
                    $plain = (string) $part->getBody();
                }
            }
            return $plain ?? '';
        }

        // SwiftMailer style body
        if (is_object($body) && method_exists($body, 'getBody')) {
            return (string) $body->getBody();
        }

        // Fallback to string
        return (string) $body ?? '';
    }
}
