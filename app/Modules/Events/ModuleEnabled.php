<?php

declare(strict_types=1);

namespace App\Modules\Events;

use App\Modules\Contracts\ModuleInterface;

class ModuleEnabled
{
    public function __construct(
        public readonly string $name,
        public readonly ModuleInterface $module,
    ) {}
}
