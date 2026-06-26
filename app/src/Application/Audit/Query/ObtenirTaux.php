<?php

declare(strict_types=1);

namespace App\Application\Audit\Query;

final readonly class ObtenirTaux
{
    public function __construct(public string $projetId)
    {
    }
}
