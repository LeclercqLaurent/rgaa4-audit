<?php

declare(strict_types=1);

namespace App\Application\Audit\Query;

final readonly class ObtenirProjet
{
    public function __construct(public string $id)
    {
    }
}
