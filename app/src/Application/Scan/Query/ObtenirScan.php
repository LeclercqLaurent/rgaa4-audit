<?php

declare(strict_types=1);

namespace App\Application\Scan\Query;

final readonly class ObtenirScan
{
    public function __construct(public string $id)
    {
    }
}
