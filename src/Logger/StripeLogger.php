<?php
namespace CarloNicora\Minimalism\Services\Stripe\Logger;
use CarloNicora\Minimalism\Interfaces\LoggerInterface as MinimalismLoggerInterface;
use CarloNicora\Minimalism\Interfaces\SimpleObjectInterface;
use Stripe\Util\LoggerInterface;

class StripeLogger implements LoggerInterface, SimpleObjectInterface
{
    /**
     * @param MinimalismLoggerInterface $logger
     */
    public function __construct(
        private MinimalismLoggerInterface $logger
    )
    {
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($message, domain: 'Stripe', context: $context);
    }

}