<?php

namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\Minimalism\Factories\ObjectFactory;
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

    public static function getBaseInterface(): ?string
    {
        return LoggerInterface::class;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($message, domain: 'Stripe', context: $context);
    }

    /**
     * @param ObjectFactory $objectFactory
     * @return void
     */
    public function setObjectFactory(ObjectFactory $objectFactory): void
    {
        // TODO are there any other way to initialise StripeLogger dependency in the Stripe service without implementing the ServiceInterface?
    }

    public function initialise(): void
    {
    }

    public function destroy(): void
    {
    }
}