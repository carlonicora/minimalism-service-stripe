<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Invoices\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Invoices\Databases\StripeInvoicesTable;
use CarloNicora\Minimalism\Services\Stripe\Data\Invoices\DataObject\StripeInvoice;

class StripeInvoiceIO extends AbstractSqlIO
{

    /**
     * @param string $stripeInvoiceId
     * @return StripeInvoice
     * @throws MinimalismException
     */
    public function byStripeInvoiceId(
        string $stripeInvoiceId
    ): StripeInvoice
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeInvoicesTable::class)
                ->selectAll()
                ->addParameter(field: StripeInvoicesTable::stripeInvoiceId, value: $stripeInvoiceId),
            responseType: StripeInvoice::class
        );
    }

    /**
     * @param int $recieperId
     * @param int $payerId
     * @return array
     * @throws MinimalismException
     */
    public function byStripeIdAndPayerIds(
        int $recieperId,
        int $payerId
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeInvoicesTable::class)
                ->selectAll()
                ->addParameter(field: StripeInvoicesTable::recieperId, value: $recieperId)
                ->addParameter(field: StripeInvoicesTable::payerId, value: $payerId)
        );
    }

    /**
     * @param StripeInvoice $dataObject
     * @return array
     */
    public function create(
        StripeInvoice $dataObject
    ): array
    {
        return $this->data->create(
            queryFactory: $dataObject
        );
    }

    /**
     * @param StripeInvoice $dataObject
     * @return void
     */
    public function update(
        StripeInvoice $dataObject
    ): void
    {
        $this->data->update(
            queryFactory: $dataObject,
        );
    }

}