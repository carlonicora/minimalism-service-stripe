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

    /**
     * The initialise method should contain all the functions that needs to
     * be run if the service is loaded from cache.
     * This can include reading user-specific parameters from the session
     */
    public function initialise(): void {}

    /**
     * The destroy method should contain all the functions that needs to
     * be run before serialising the service to cache.
     * This should include the removal of all the user-specific parameters
     * from the service and add them to the session
     */
    public function destroy(): void {}

}