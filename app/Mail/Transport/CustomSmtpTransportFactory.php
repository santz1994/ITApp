<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class CustomSmtpTransportFactory implements TransportFactoryInterface
{
    public function __construct(
        private ?EventDispatcherInterface $dispatcher = null,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function create(Dsn $dsn): \Symfony\Component\Mailer\Transport\TransportInterface
    {
        $host = $dsn->getHost();
        $port = $dsn->getPort(465);
        $tls = 'smtps' === $dsn->getScheme();

        $transport = new EsmtpTransport($host, $port, $tls, $this->dispatcher, $this->logger);

        if ($user = $dsn->getUser()) {
            $transport->setUsername($user);
        }

        if ($password = $dsn->getPassword()) {
            $transport->setPassword($password);
        }

        // Try to access and modify stream options
        try {
            $reflection = new \ReflectionClass($transport);
            
            // Try to find stream property with different possible names
            $streamProperty = null;
            foreach (['stream', '_stream', 'socketStream'] as $propName) {
                if ($reflection->hasProperty($propName)) {
                    $streamProperty = $reflection->getProperty($propName);
                    break;
                }
            }
            
            if ($streamProperty) {
                $streamProperty->setAccessible(true);
                $stream = $streamProperty->getValue($transport);

                if ($stream && method_exists($stream, 'setStreamOptions')) {
                    $stream->setStreamOptions([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                        ],
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail - will use default SSL behavior
        }

        return $transport;
    }

    public function supports(Dsn $dsn): bool
    {
        return in_array($dsn->getScheme(), $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['smtp', 'smtps'];
    }
}
