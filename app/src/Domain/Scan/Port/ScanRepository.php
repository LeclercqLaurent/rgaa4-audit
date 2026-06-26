<?php

declare(strict_types=1);

namespace App\Domain\Scan\Port;

use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\ValueObject\ResumeScan;

interface ScanRepository
{
    /**
     * Dernier scan (résumé) de chaque projet.
     *
     * @return array<string, ResumeScan> indexé par identifiant de projet
     */
    public function dernierResumeParProjet(): array;

    public function save(Scan $scan): void;

    public function get(string $id): ?Scan;

    /**
     * @return list<Scan>
     */
    public function findByProjet(string $projetId): array;

    public function supprimerPourProjet(string $projetId): void;
}
