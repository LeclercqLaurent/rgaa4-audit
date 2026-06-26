<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity\Adapter;

use App\Domain\Identity\Entity\Utilisateur;
use App\Domain\Identity\Port\UtilisateurRepository;
use App\Domain\Identity\ValueObject\Email;
use App\Domain\Identity\ValueObject\RoleUtilisateur;
use App\Infrastructure\Persistence\Entity\UtilisateurEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUtilisateurRepository implements UtilisateurRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Utilisateur $utilisateur): void
    {
        $roles = array_map(static fn (RoleUtilisateur $r): string => $r->value, $utilisateur->roles);
        $this->em->persist(new UtilisateurEntity($utilisateur->id, (string) $utilisateur->email, $utilisateur->motDePasseHache, $roles));
        $this->em->flush();
    }

    public function parEmail(string $email): ?Utilisateur
    {
        $entity = $this->em->getRepository(UtilisateurEntity::class)->findOneBy(['email' => mb_strtolower(trim($email))]);

        return $entity instanceof UtilisateurEntity ? $this->toDomain($entity) : null;
    }

    private function toDomain(UtilisateurEntity $entity): Utilisateur
    {
        $roles = array_map(static fn (string $r): RoleUtilisateur => RoleUtilisateur::from($r), $entity->getRoles());

        if ([] === $roles) {
            $roles = [RoleUtilisateur::Auditeur];
        }

        return new Utilisateur($entity->getId(), new Email($entity->getEmail()), $entity->getMotDePasse(), $roles);
    }
}
