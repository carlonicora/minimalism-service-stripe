<?php
namespace CarloNicora\Minimalism\Services\Stripe\Data\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use Exception;

class UserBuilder extends AbstractResourceBuilder
{
    /** @var string  */
    public string $type = 'user';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->id = $this->encrypter->encryptId($data['userId']);
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function setMeta(
        array $data
    ): void
    {
        parent::setMeta($data);

        $this->response->meta->add('forceGet', true);
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function setLinks(
        array $data
    ): void
    {
        $this->response->links->add(
            new Link(
                'self',
                $this->path->getUrl()
                . 'users/'
                . $this->encrypter->encryptId($data['userId'])
            )
        );
    }
}