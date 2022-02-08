<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeCustomersTable;
use Exception;

class StripeCustomerIO extends AbstractLoader
{

    /**
     * @param int $userId
     * @return array
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function byUserId(
        int $userId
    ): array
    {
        /** @see StripeCustomersTable::readById() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeCustomersTable::class,
            functionName: 'readById',
            parameters: [
                'byId' => $userId
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe customer');
    }

    /**
     * @param int $customerId
     * @return array
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function byCustomerId(
        int $customerId
    ): array
    {
        /** @see StripeCustomersTable::byCustomerId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeCustomersTable::class,
            functionName: 'byCustomerId',
            parameters: [
                'customerId' => $customerId
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe customer');
    }

    /**
     * @param int $userId
     * @param string $stripeCustomerId
     * @param string $email
     * @return array
     */
    public function create(
        int    $userId,
        string $stripeCustomerId,
        string $email
    ): array
    {
        $records = [
            'userId' => $userId,
            'stripeCustomerId' => $stripeCustomerId,
            'email' => $email,
            'createdAt' => date(format: 'Y-m-d H:i:s'),
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeCustomersTable::class,
            records: $records
        );
    }
}