<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_constat')]
#[ORM\UniqueConstraint(name: 'uniq_constat', columns: ['projet_id', 'referentiel', 'page_url', 'critere_numero', 'source'])]
#[ORM\Index(name: 'idx_constat_projet', columns: ['projet_id'])]
class ConstatEntity
{
    /**
     * @var list<array<string, mixed>>
     */
    #[ORM\Column(type: 'json')]
    private array $preuves = [];

    #[ORM\Column(type: 'text', nullable: true, options: ['collation' => 'utf8mb4_uca1400_ai_ci'])]
    private ?string $commentaire = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $id,
        #[ORM\Column(name: 'projet_id', length: 36, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $projetId,
        #[ORM\Column(length: 20, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $referentiel,
        #[ORM\Column(name: 'page_url', length: 2048)]
        private string $pageUrl,
        #[ORM\Column(name: 'critere_numero', length: 20, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $critereNumero,
        #[ORM\Column(length: 16, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $statut,
        #[ORM\Column(length: 8, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $source,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $preuves
     */
    public function setPreuves(array $preuves): void
    {
        $this->preuves = $preuves;
    }

    public function setCommentaire(?string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProjetId(): string
    {
        return $this->projetId;
    }

    public function getReferentiel(): string
    {
        return $this->referentiel;
    }

    public function getPageUrl(): string
    {
        return $this->pageUrl;
    }

    public function getCritereNumero(): string
    {
        return $this->critereNumero;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPreuves(): array
    {
        return $this->preuves;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }
}
