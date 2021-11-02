<?php

namespace CarloNicora\Minimalism\Services\Stripe\Traits;

use CarloNicora\Minimalism\Interfaces\DataLoaderInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeAccountsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripePaymentsDataWriter;
use Exception;

trait StripeLoaders
{
    /** @var array  */
    protected array $dataLoaders;

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
     * @return StripePaymentsDataWriter|DataLoaderInterface
     * @throws Exception
     */
    public function getPaymentsDataWriter(): StripePaymentsDataWriter|DataLoaderInterface
    {
        return $this->getDataLoader(
            dataLoaderName: StripePaymentsDataWriter::class
        );
    }
}