<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataReaders;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use JetBrains\PhpStorm\ArrayShape;

class UsersDataReader extends AbstractLoader
{
    #[ArrayShape(['userId' => "int"])]
    /**
     * @param int $userId
     * @return array
     */
    public function byUserId(
        int $userId
    ): array
    {
        return [
            'userId' => $userId
        ];
    }
}