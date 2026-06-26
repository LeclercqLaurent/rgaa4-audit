<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

final readonly class SupprimerProjet
{
    public function __construct(public string $id)
    {
    }
}
