<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Association axe-core → RGAA : pour un tag axe (`wcagXYZ`), un critère RGAA
 * concerné. Table de référence dérivée (mapping-wcag-rgaa.yaml).
 */
#[ORM\Entity]
#[ORM\Table(name: 'rgaa_mapping_axe')]
class MappingAxeEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(name: 'axe_tag', length: 32, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $axeTag,
        #[ORM\Id]
        #[ORM\Column(name: 'critere_numero', length: 8, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $critereNumero,
    ) {
    }

    public function getAxeTag(): string
    {
        return $this->axeTag;
    }

    public function getCritereNumero(): string
    {
        return $this->critereNumero;
    }
}
