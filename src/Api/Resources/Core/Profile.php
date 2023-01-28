<?php

namespace KyleWLawrence\Infinity\Api\Resources\Core;

use KyleWLawrence\Infinity\Api\Resources\ResourceAbstract;

/**
 * The Profile class exposes key methods for getting the current profile
 *
 * @method Profile profile()
 */
class Profile extends ResourceAbstract
{
    /**
     * @var bool
     */
    protected bool $includeWorkspace = false;

    /**
     * {@inheritdoc}
     */
    public static function getValidSubResources(): array
    {
        return [
        ];
    }

    /**
     * Declares routes to be used by this resource.
     */
    protected function setUpRoutes(): void
    {
        parent::setUpRoutes();

        $this->setRoutes([
            'getCurrent' => 'profile',
        ]);
    }

    /**
     * Get the current profile
     *
     * @return null|\stdClass
     */
    public function getCurrent(): ?object
    {
        $route = $this->getRoute(__FUNCTION__);

        return $this->client->get(
            $route
        );
    }
}
