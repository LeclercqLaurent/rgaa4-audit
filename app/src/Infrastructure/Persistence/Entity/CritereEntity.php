<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rgaa_critere')]
class CritereEntity
{
    /**
     * @var list<array{sc: string, intitule: string, niveau: string, axe_tag: string}>
     */
    #[ORM\Column(type: 'json')]
    private array $wcag;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    private array $techniques;

    /**
     * @var Collection<int, TestEntity>
     */
    #[ORM\OneToMany(targetEntity: TestEntity::class, mappedBy: 'critere', cascade: ['persist'], orphanRemoval: true)]
    private Collection $tests;

    /**
     * @param list<array{sc: string, intitule: string, niveau: string, axe_tag: string}> $wcag
     * @param list<string>                                                                $techniques
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 8, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $numero,
        #[ORM\ManyToOne(targetEntity: ThematiqueEntity::class, inversedBy: 'criteres')]
        #[ORM\JoinColumn(name: 'thematique_numero', referencedColumnName: 'numero', nullable: false)]
        private ThematiqueEntity $thematique,
        #[ORM\Column(type: 'text', options: ['collation' => 'utf8mb4_uca1400_ai_ci'])]
        private string $intitule,
        array $wcag,
        array $techniques,
    ) {
        $this->wcag = $wcag;
        $this->techniques = $techniques;
        $this->tests = new ArrayCollection();
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function getIntitule(): string
    {
        return $this->intitule;
    }

    /**
     * @return list<array{sc: string, intitule: string, niveau: string, axe_tag: string}>
     */
    public function getWcag(): array
    {
        return $this->wcag;
    }

    /**
     * @return list<string>
     */
    public function getTechniques(): array
    {
        return $this->techniques;
    }

    /**
     * @return Collection<int, TestEntity>
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(TestEntity $test): void
    {
        if (!$this->tests->contains($test)) {
            $this->tests->add($test);
        }
    }
}
