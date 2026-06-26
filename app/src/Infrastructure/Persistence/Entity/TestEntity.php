<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rgaa_test')]
class TestEntity
{
    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    private array $enonces;

    /**
     * @param list<string> $enonces
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 12, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $numero,
        #[ORM\ManyToOne(targetEntity: CritereEntity::class, inversedBy: 'tests')]
        #[ORM\JoinColumn(name: 'critere_numero', referencedColumnName: 'numero', nullable: false, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private CritereEntity $critere,
        array $enonces,
    ) {
        $this->enonces = $enonces;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    /**
     * @return list<string>
     */
    public function getEnonces(): array
    {
        return $this->enonces;
    }
}
