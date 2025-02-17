<?php

declare(strict_types=1);

namespace KyleWLawrence\Infinity\Data\Objects;

use KyleWLawrence\Infinity\Data\Objects\Traits\Settings;

class View extends ObjectBase
{
    use Settings;

    protected string $name;

    protected object $settings;

    protected string $folder_id;

    protected string $type;

    protected ?float $sort_order = 0.0;

    protected ?string $parent_id = null;

    protected array $required = ['name', 'type', 'folder_id'];

    protected array $update_vars = ['name', 'folder_id', 'settings', 'type', 'sort_order'];

    protected ?string $object = 'folderview';

    protected string $obj_name = 'view';

    protected string $obj_name_plural = 'views';
}
