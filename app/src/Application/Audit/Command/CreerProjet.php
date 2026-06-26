<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\ValueObject\Referentiel;

final readonly class CreerProjet
{
    public function __construct(
        public string $nom,
        public string $client,
        public Referentiel $type,
        public string $cible,
    ) {
    }
}
