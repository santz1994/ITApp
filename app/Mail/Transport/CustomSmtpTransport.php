<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class CustomSmtpTransport extends EsmtpTransport
{
    public function __construct(string $host = 'localhost', int $port = 25, bool $tls = false, \Psr\Log\LoggerInterface $logger = null)
    {
        parent::__construct($host, $port, $tls, null, $logger);
        
        // Override the stream to add custom SSL context options
        $reflection = new \ReflectionClass(parent::class);
        $streamProperty = $reflection->getProperty('stream');
        $streamProperty->setAccessible(true);
        
        $stream = $streamProperty->getValue($this);
        if ($stream instanceof SocketStream) {
            $streamOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
            $stream->setStreamOptions($streamOptions);
        }
    }
}
