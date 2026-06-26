<?php

declare(strict_types=1);

namespace App\Domain\Audit\Entity;

use App\Domain\Audit\ValueObject\Preuve;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;

/**
 * Constat de conformité d'un critère pour une unité auditée d'un projet
 * (une page web pour RGAA, une méthode pour la complexité…).
 *
 * Un constat « auto » est proposé par le moteur ; un constat « manuel » est
 * établi par l'auditeur et fait foi (cf. lot A4).
 */
final class Constat
{
    private ?string $commentaire = null;

    /**
     * @param list<Preuve> $preuves
     */
    public function __construct(
        private readonly string $projetId,
        private readonly Referentiel $referentiel,
        private readonly string $uniteAuditee,
        private readonly string $critereNumero,
        private StatutConformite $statut,
        private SourceConstat $source,
        private array $preuves = [],
    ) {
    }

    public function avecCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function projetId(): string
    {
        return $this->projetId;
    }

    public function referentiel(): Referentiel
    {
        return $this->referentiel;
    }

    public function uniteAuditee(): string
    {
        return $this->uniteAuditee;
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
