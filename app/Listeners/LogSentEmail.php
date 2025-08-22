<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;

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
    public function handle(MessageSent $event)
    {
        $message = $event->message;

        // Get recipients - handle both regular emails and password reset emails
        $to = $this->getRecipients($message, $event);

        // Get email content
        $body = $this->getEmailContent($message);

        // Store the email details
        EmailLog::create([
            'to' => $to,
            'subject' => $message->getSubject() ?? '(No Subject)',
            'body' => $body,
            'status' => 'sent',
        ]);
    }

    /**
     * Extract recipients from the message.
     *
     * @param  \Swift_Message|\Symfony\Component\Mime\Email  $message
     * @param  \Illuminate\Mail\Events\MessageSent  $event
     * @return string
     */
    protected function getRecipients($message, $event)
    {
        // For password reset emails and some other system emails
        if (isset($event->data['email'])) {
            return $event->data['email'];
        }

        // For Symfony Mailer (Laravel 7+)
        $to = $message->getTo();
        if (is_array($to) && !empty($to)) {
            return implode(',', array_map(function ($address) {
                return $address instanceof \Symfony\Component\Mime\Address
                    ? $address->getAddress()
                    : $address;
            }, $to));
        }

        // Fallback for cases where getTo() doesn't return expected data
        if (method_exists($message, 'getEnvelope')) {
            $envelope = $message->getEnvelope();
            $recipients = $envelope->getRecipients();
            if (!empty($recipients)) {
                return implode(',', array_map(function ($recipient) {
                    return $recipient->getAddress();
                }, $recipients));
            }
        }

        return 'unknown@example.com';
    }

    /**
     * Extract email content from the message.
     *
     * @param  \Swift_Message|\Symfony\Component\Mime\Email  $message
     * @return string
     */
    protected function getEmailContent($message)
    {
        $body = $message->getBody();

        if (is_object($body) && method_exists($body, 'getParts')) {
            $parts = $body->getParts();
            $content = '';

            foreach ($parts as $part) {
                if ($part->getMediaType() === EmailLog::TYPE_TEXT && $part->getMediaSubtype() === EmailLog::TYPE_HTML) {
                    return $part->getBody();
                }
                if ($part->getMediaType() === EmailLog::TYPE_TEXT && $part->getMediaSubtype() === EmailLog::TYPE_PLAIN) {
                    $content = $part->getBody();
                }
            }

            return $content;
        }

        // If $body is an object but not multipart, get body content string
        if (is_object($body) && method_exists($body, 'getBody')) {
            return $body->getBody();
        }

        // Otherwise, assume it's a string
        return (string) $body;
    }
}
