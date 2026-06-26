<?php

declare(strict_types=1);

namespace App\Domain\Audit\Port;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\Referentiel;

interface ConstatRepository
{
    /**
     * Remplace les constats automatiques d'un projet pour un référentiel donné
     * (les constats manuels et les autres référentiels ne sont pas touchés).
     *
     * @param list<Constat> $constats
     */
    public function remplacerAuto(string $projetId, Referentiel $referentiel, array $constats): void;

    /**
     * Enregistre (ou remplace) le constat manuel d'un (projet, page, critère).
     */
    public function enregistrerManuel(Constat $constat): void;

    /**
     * @return list<Constat>
     */
    public function findByProjet(string $projetId): array;

    public function supprimerPourProjet(string $projetId): void;
}
