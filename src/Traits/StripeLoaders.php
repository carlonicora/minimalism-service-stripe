<?php

namespace CarloNicora\Minimalism\Services\Stripe\Traits;

use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeAccountsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeAccountsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripePaymentIntentsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\ResourceReaders\StripePaymentIntentsResourceReader;
use Exception;

trait StripeLoaders
{
    /** @var array  */
    protected array $dataLoaders = [];

    /**
     * @param string $dataLoaderName
     * @return StripeAccountsDataWriter|StripeAccountsDataReader|StripePaymentIntentsDataWriter|StripePaymentIntentsResourceReader
     * @throws Exception
     */
    private function getDataLoader(
        string $dataLoaderName
    ): StripeAccountsDataWriter|StripeAccountsDataReader|StripePaymentIntentsDataWriter|StripePaymentIntentsResourceReader
    {
        if (!array_key_exists($dataLoaderName, $this->dataLoaders)){
            $this->dataLoaders[$dataLoaderName] = $this->objectFactory->create(
                className: $dataLoaderName
            );
        }

        return $this->dataLoaders[$dataLoaderName];
    }

    /**
     * @return StripeAccountsDataWriter
     * @throws Exception
     */
    public function getAccountsDataWriter(): StripeAccountsDataWriter
    {
        return $this->getDataLoader(
            dataLoaderName: StripeAccountsDataWriter::class
        );
    }

    /**
     * @return StripeAccountsDataReader
     * @throws Exception
     */
    public function getAccountsDataReader(): StripeAccountsDataReader
    {
        return $this->getDataLoader(
            dataLoaderName: StripeAccountsDataReader::class
        );
    }

    /**
     * @return StripePaymentIntentsDataWriter
     * @throws Exception
     */
    public function getPaymentIntentsDataWriter(): StripePaymentIntentsDataWriter
    {
        return $this->getDataLoader(
            dataLoaderName: StripePaymentIntentsDataWriter::class
        );
    }

    /**
     * @return StripePaymentIntentsResourceReader
     * @throws Exception
     */
    public function getPaymentIntentsResourceReader(): StripePaymentIntentsResourceReader
    {
        return $this->getDataLoader(
            dataLoaderName: StripePaymentIntentsResourceReader::class
        );
    }
}