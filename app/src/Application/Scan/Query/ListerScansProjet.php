<?php

declare(strict_types=1);

namespace App\Application\Scan\Query;

final readonly class ListerScansProjet
{
    public function __construct(public string $projetId)
    {
    }
}
