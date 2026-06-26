<?php

declare(strict_types=1);

namespace App\Domain\Scan\Port;

use App\Domain\Scan\Entity\Scan;

interface ScanRepository
{
    public function save(Scan $scan): void;

    public function get(string $id): ?Scan;

    /**
     * @return list<Scan>
     */
    public function findByProjet(string $projetId): array;
}
