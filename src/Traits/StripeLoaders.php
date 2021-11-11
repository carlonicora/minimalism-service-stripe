<?php

namespace CarloNicora\Minimalism\Services\Stripe\Traits;

use CarloNicora\Minimalism\Interfaces\DataLoaderInterface;
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
     * @return DataLoaderInterface
     * @throws Exception
     */
    private function getDataLoader(
        string $dataLoaderName
    ): DataLoaderInterface
    {
        if (!array_key_exists($dataLoaderName, $this->dataLoaders)){
            $this->dataLoaders[$dataLoaderName] = $this->pools->get(
                className: $dataLoaderName
            );
        }

        return $this->dataLoaders[$dataLoaderName];
    }

    /**
     * @return StripeAccountsDataWriter|DataLoaderInterface
     * @throws Exception
     */
    public function getAccountsDataWriter(): StripeAccountsDataWriter|DataLoaderInterface
    {
        return $this->getDataLoader(
            dataLoaderName: StripeAccountsDataWriter::class
        );
    }

    /**
     * @return StripeAccountsDataReader|DataLoaderInterface
     * @throws Exception
     */
    public function getAccountsDataReader(): StripeAccountsDataReader|DataLoaderInterface
    {
        return $this->getDataLoader(
            dataLoaderName: StripeAccountsDataReader::class
        );
    }

    /**
     * @return StripePaymentIntentsDataWriter|DataLoaderInterface
     * @throws Exception
     */
    public function getPaymentIntentsDataWriter(): StripePaymentIntentsDataWriter|DataLoaderInterface
    {
        return $this->getDataLoader(
            dataLoaderName: StripePaymentIntentsDataWriter::class
        );
    }

    /**
     * @return StripePaymentIntentsResourceReader|DataLoaderInterface
     * @throws Exception
     */
    public function getPaymentIntentsResourceReader(): StripePaymentIntentsResourceReader|DataLoaderInterface
    {
        return $this->getDataLoader(
            dataLoaderName: StripePaymentIntentsResourceReader::class
        );
    }
}