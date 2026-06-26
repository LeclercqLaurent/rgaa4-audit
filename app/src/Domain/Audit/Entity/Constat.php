<?php

declare(strict_types=1);

namespace App\Domain\Audit\Entity;

use App\Domain\Audit\ValueObject\Preuve;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;

/**
 * Constat de conformité d'un critère RGAA pour une page d'un projet.
 *
 * Un constat « auto » est proposé par le scan ; un constat « manuel » est établi
 * par l'auditeur et fait foi (cf. lot A4).
 */
final class Constat
{
    /**
     * @param list<Preuve> $preuves
     */
    public function __construct(
        private readonly string $projetId,
        private readonly string $pageUrl,
        private readonly string $critereNumero,
        private StatutConformite $statut,
        private SourceConstat $source,
        private array $preuves = [],
        private ?string $commentaire = null,
    ) {
    }

    public function projetId(): string
    {
        return $this->projetId;
    }

    public function pageUrl(): string
    {
        return $this->pageUrl;
    }

    public function critereNumero(): string
    {
        return $this->critereNumero;
    }

    public function statut(): StatutConformite
    {
        return $this->statut;
    }

    public function source(): SourceConstat
    {
        return $this->source;
    }

    /**
     * @return list<Preuve>
     */
    public function preuves(): array
    {
        return $this->preuves;
    }

    public function commentaire(): ?string
    {
        return $this->commentaire;
    }
}
