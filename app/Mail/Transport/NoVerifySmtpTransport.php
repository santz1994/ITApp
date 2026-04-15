<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

class NoVerifySmtpTransport implements TransportInterface
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;

    public function __construct(array $config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->encryption = $config['encryption'] ?? 'ssl';
    }

    public function send(RawMessage $message, ?\Symfony\Component\Mailer\Envelope $envelope = null): ?SentMessage
    {
        // Get message details
        $recipients = $envelope ? $envelope->getRecipients() : $message->getTo();
        $to = is_array($recipients) ? array_shift($recipients)->getAddress() : $recipients->getAddress();
        
        // Create SSL context that skips verification
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        // Connect based on encryption type
        if ($this->encryption === 'ssl') {
            $socket = @stream_socket_client(
                "ssl://{$this->host}:{$this->port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
        } else {
            $socket = @stream_socket_client(
                "{$this->host}:{$this->port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
        }

        if (!$socket) {
            throw new \Exception("Failed to connect to {$this->host}:{$this->port} - {$errstr} ({$errno})");
        }

        try {
            // Read greeting
            fgets($socket);

            // EHLO
            fputs($socket, "EHLO localhost\r\n");
            while ($line = fgets($socket)) {
                if (preg_match('/^\d{3} /', $line)) break;
            }

            // AUTH LOGIN
            fputs($socket, "AUTH LOGIN\r\n");
            fgets($socket);

            fputs($socket, base64_encode($this->username) . "\r\n");
            fgets($socket);

            fputs($socket, base64_encode($this->password) . "\r\n");
            $auth_response = trim(fgets($socket));

            if (strpos($auth_response, '235') === false) {
                throw new \Exception("Authentication failed: {$auth_response}");
            }

            // MAIL FROM
            fputs($socket, "MAIL FROM:<{$this->username}>\r\n");
            fgets($socket);

            // RCPT TO
            fputs($socket, "RCPT TO:<{$to}>\r\n");
            fgets($socket);

            // DATA
            fputs($socket, "DATA\r\n");
            fgets($socket);

            // Send message
            fputs($socket, $message->toString());
            fputs($socket, "\r\n.\r\n");
            fgets($socket);

            // QUIT
            fputs($socket, "QUIT\r\n");
            fgets($socket);

            fclose($socket);

            return new SentMessage($message, $envelope ?? new \Symfony\Component\Mime\Address($this->username));

        } catch (\Exception $e) {
            if (is_resource($socket)) {
                fclose($socket);
            }
            throw $e;
        }
    }

    public function __toString(): string
    {
        return sprintf('noverifysmtp://%s@%s:%d', $this->username, $this->host, $this->port);
    }
}
