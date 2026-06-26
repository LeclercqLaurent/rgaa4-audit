<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'scan')]
class ScanEntity
{
    /**
     * @param list<array<string, mixed>> $resultats Résultats axe-core par page (forme sérialisée)
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $id,
        #[ORM\Column(name: 'projet_id', length: 36, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $projetId,
        #[ORM\Column(length: 12, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $statut,
        #[ORM\Column(name: 'date_creation', type: 'datetime_immutable')]
        private DateTimeImmutable $dateCreation,
        #[ORM\Column(type: 'json')]
        private array $resultats = [],
        #[ORM\Column(name: 'date_fin', type: 'datetime_immutable', nullable: true)]
        private ?DateTimeImmutable $dateFin = null,
        #[ORM\Column(type: 'text', nullable: true, options: ['collation' => 'utf8mb4_uca1400_ai_ci'])]
        private ?string $erreur = null,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $resultats
     */
    public function maj(string $statut, array $resultats, ?DateTimeImmutable $dateFin, ?string $erreur): void
    {
        $this->statut = $statut;
        $this->resultats = $resultats;
        $this->dateFin = $dateFin;
        $this->erreur = $erreur;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProjetId(): string
    {
        return $this->projetId;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getDateCreation(): DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function getDateFin(): ?DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function getErreur(): ?string
    {
        return $this->erreur;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getResultats(): array
    {
        return $this->resultats;
    }
}
