<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'utilisateur')]
#[ORM\UniqueConstraint(name: 'uniq_utilisateur_email', columns: ['email'])]
class UtilisateurEntity
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36, options: ['charset' => 'ascii', 'collation' => 'ascii_bin'])]
        private string $id,
        #[ORM\Column(length: 180)]
        private string $email,
        #[ORM\Column(name: 'mot_de_passe', length: 255)]
        private string $motDePasse,
        #[ORM\Column(type: 'json')]
        private array $roles,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
