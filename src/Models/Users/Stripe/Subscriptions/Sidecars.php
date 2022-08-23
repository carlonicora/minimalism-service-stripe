<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Users\Stripe\Subscriptions;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\User\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;

class Sidecars extends AbstractModel
{

    /**
     * @param Stripe $stripe
     * @param UserServiceInterface $currentUser
     * @param PositionedEncryptedParameter $recieperParam
     * @return HttpCode
     * @throws MinimalismException
     * @throws Exception
     */
    public function get(
        Stripe                       $stripe,
        UserServiceInterface         $currentUser,
        PositionedEncryptedParameter $recieperParam,
    ): HttpCode
    {
        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Access not allowed to guests');
        }

        $this->document = $stripe->getSubscriptionSidecar(
            recieperId: $recieperParam->getValue(),
            payerId: $currentUser->getId()
        );

        return HttpCode::Ok;
    }

}