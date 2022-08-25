<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Payers;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use CarloNicora\Minimalism\Services\Users\Users;

class Tips extends AbstractModel
{

    /**
     * @param Stripe $stripe
     * @param Users $userService
     * @param int|null $offset
     * @param int|null $length
     * @return HttpCode
     * @throws MinimalismException
     */
    public function get(
        Stripe $stripe,
        Users  $userService,
        ?int   $offset = 0,
        ?int   $length = 10
    ): HttpCode
    {
        $userService->load();
        if ($userService->isVisitor()) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Access not allowed to guests');
        }

        $this->document = $stripe->getPayerTips(
            payerId: $userService->getId(),
            offset: $offset,
            limit: $length
        );

        return HttpCode::Ok;
    }

}