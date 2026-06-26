<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

final readonly class SupprimerPage
{
    public function __construct(
        public string $projetId,
        public string $pageId,
    ) {
    }
}
