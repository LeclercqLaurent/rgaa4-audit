<?php

declare(strict_types=1);

namespace App\Application\Reporting\Query;

final readonly class ObtenirRapport
{
    public function __construct(public string $projetId)
    {
    }
}
