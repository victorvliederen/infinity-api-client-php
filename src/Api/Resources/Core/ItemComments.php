<?php

namespace KyleWLawrence\Infinity\Api\Resources\Core;

use KyleWLawrence\Infinity\Api\Resources\ResourceAbstract;
use KyleWLawrence\Infinity\Api\Traits\Resource\Defaults;

/**
 * The ItemComments class exposes comment methods for item comments
 */
class ItemComments extends ResourceAbstract
{
    use Defaults;

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'comment';

    /**
     * {@inheritdoc}
     */
    protected $objectNamePlural = 'comments';

    /**
     * {@inherticdoc}
     */
    public function getAdditionalRouteParams(): array
    {
        $board = $this->getLaestChainedParameter();
        $item = $this->getLatestChainedParameter(['Infinity\Api\Resources\Core\Items']);
        $boardParam = [
            'board_id' => reset($board),
            'item_id' => reset($item),
        ];

        return array_merge($boardParam, $this->additionalRouteParams);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes(): void
    {
        $this->setRoutes(
            [
                'getAllLoop' => 'boards/{board_id}/items/{item_id}/comments',
                'getAll' => 'boards/{board_id}/items/{item_id}/comments',
                'get' => 'boards/{board_id}/items/{item_id}/comments/{id}',
                'create' => 'boards/{board_id}/items/{item_id}/comments',
                'update' => 'boards/{board_id}/items/{item_id}/comments/{id}',
                'delete' => 'boards/{board_id}/items/{item_id}/comments/{id}',
            ]
        );
    }
}
