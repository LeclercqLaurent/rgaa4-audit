<?php

declare(strict_types=1);

namespace App\Domain\Audit\Port;

use App\Domain\Audit\Entity\Constat;

interface ConstatRepository
{
    /**
     * Remplace l'intégralité des constats automatiques d'un projet (les constats
     * manuels de l'auditeur ne sont pas touchés).
     *
     * @param list<Constat> $constats
     */
    public function remplacerAuto(string $projetId, array $constats): void;

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
