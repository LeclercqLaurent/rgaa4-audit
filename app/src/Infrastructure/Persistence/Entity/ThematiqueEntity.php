<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rgaa_thematique')]
class ThematiqueEntity
{
    /**
     * @var Collection<int, CritereEntity>
     */
    #[ORM\OneToMany(targetEntity: CritereEntity::class, mappedBy: 'thematique', cascade: ['persist'], orphanRemoval: true)]
    private Collection $criteres;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $numero,
        #[ORM\Column(length: 100, options: ['collation' => 'utf8mb4_uca1400_ai_ci'])]
        private string $nom,
    ) {
        $this->criteres = new ArrayCollection();
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @return Collection<int, CritereEntity>
     */
    public function getCriteres(): Collection
    {
        return $this->criteres;
    }

    public function addCritere(CritereEntity $critere): void
    {
        if (!$this->criteres->contains($critere)) {
            $this->criteres->add($critere);
        }
    }
}
