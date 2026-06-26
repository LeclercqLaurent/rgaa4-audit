<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_page')]
class PageEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $id,
        #[ORM\Column(length: 2048)]
        private string $url,
        #[ORM\Column(length: 255, options: ['collation' => 'utf8mb4_uca1400_ai_ci'])]
        private string $titre,
        #[ORM\ManyToOne(targetEntity: ProjetEntity::class, inversedBy: 'pages')]
        #[ORM\JoinColumn(name: 'projet_id', referencedColumnName: 'id', nullable: false, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private ProjetEntity $projet,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getProjet(): ProjetEntity
    {
        return $this->projet;
    }
}
