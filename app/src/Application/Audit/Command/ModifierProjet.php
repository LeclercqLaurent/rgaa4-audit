<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

final readonly class ModifierProjet
{
    public function __construct(
        public string $id,
        public string $nom,
        public string $client,
        public string $cible,
    ) {
    }
}
