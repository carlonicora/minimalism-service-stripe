<?php

namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\Minimalism\Interfaces\LoggerInterface as MinimalismLoggerInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use Stripe\Util\LoggerInterface;

class StripeLogger implements LoggerInterface, ServiceInterface
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

    public function initialise(): void
    {
    }

    public function destroy(): void
    {
    }
}