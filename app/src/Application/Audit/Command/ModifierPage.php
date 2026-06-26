<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

final readonly class ModifierPage
{
    public function __construct(
        public string $projetId,
        public string $pageId,
        public string $url,
        public string $titre,
    ) {
    }
}
