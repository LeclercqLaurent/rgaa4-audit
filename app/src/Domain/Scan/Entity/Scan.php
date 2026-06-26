<?php

declare(strict_types=1);

namespace App\Domain\Scan\Entity;

use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\StatutScan;
use DateTimeImmutable;

/**
 * Scan automatique d'un projet d'audit : racine d'agrégat qui porte le cycle de
 * vie (asynchrone) et collecte le résultat axe-core de chaque page.
 */
final class Scan
{
    private StatutScan $statut;

    /**
     * @var list<ResultatPage>
     */
    private array $resultats = [];

    private ?string $erreur = null;

    private ?DateTimeImmutable $dateFin = null;

    public function __construct(
        private readonly string $id,
        private readonly string $projetId,
        private readonly DateTimeImmutable $dateCreation,
        StatutScan $statut = StatutScan::EnAttente,
    ) {
        $this->statut = $statut;
    }

    /**
     * Réhydrate un scan persisté sans rejouer ses transitions de cycle de vie.
     *
     * @param list<ResultatPage> $resultats
     */
    public static function reconstituer(
        string $id,
        string $projetId,
        DateTimeImmutable $dateCreation,
        StatutScan $statut,
        ?DateTimeImmutable $dateFin,
        ?string $erreur,
        array $resultats,
    ): self {
        $scan = new self($id, $projetId, $dateCreation, $statut);
        $scan->dateFin = $dateFin;
        $scan->erreur = $erreur;
        $scan->resultats = $resultats;

        return $scan;
    }

    public function demarrer(): void
    {
        $this->statut = StatutScan::EnCours;
    }

    public function ajouterResultat(ResultatPage $resultat): void
    {
        $this->resultats[] = $resultat;
    }

    public function terminer(DateTimeImmutable $a): void
    {
        $this->statut = StatutScan::Termine;
        $this->dateFin = $a;
    }

    public function echouer(string $message, DateTimeImmutable $a): void
    {
        $this->statut = StatutScan::Echoue;
        $this->erreur = $message;
        $this->dateFin = $a;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function projetId(): string
    {
        return $this->projetId;
    }

    public function statut(): StatutScan
    {
        return $this->statut;
    }

    public function dateCreation(): DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function dateFin(): ?DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function erreur(): ?string
    {
        return $this->erreur;
    }

    /**
     * @return list<ResultatPage>
     */
    public function resultats(): array
    {
        return $this->resultats;
    }
}
