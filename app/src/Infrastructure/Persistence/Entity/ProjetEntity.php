<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_projet')]
class ProjetEntity
{
    /**
     * @var Collection<int, PageEntity>
     */
    #[ORM\OneToMany(targetEntity: PageEntity::class, mappedBy: 'projet', cascade: ['persist'], orphanRemoval: true)]
    private Collection $pages;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $id,
        #[ORM\Column(length: 150, options: ['collation' => 'utf8mb4_uca1400_ai_ci'])]
        private string $nom,
        #[ORM\Column(length: 150, options: ['collation' => 'utf8mb4_uca1400_ai_ci'])]
        private string $client,
        #[ORM\Column(name: 'url_reference', length: 2048)]
        private string $urlReference,
        #[ORM\Column(name: 'date_creation', type: 'datetime_immutable')]
        private DateTimeImmutable $dateCreation,
    ) {
        $this->pages = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getClient(): string
    {
        return $this->client;
    }

    public function getUrlReference(): string
    {
        return $this->urlReference;
    }

    public function getDateCreation(): DateTimeImmutable
    {
        return $this->dateCreation;
    }

    /**
     * @return Collection<int, PageEntity>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(PageEntity $page): void
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
        }
    }
}
