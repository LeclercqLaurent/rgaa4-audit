<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

final readonly class CreerProjet
{
    public function __construct(
        public string $nom,
        public string $client,
        public string $urlReference,
    ) {
    }
}
