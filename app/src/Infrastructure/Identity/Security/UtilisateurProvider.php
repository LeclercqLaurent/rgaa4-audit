<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity\Security;

use App\Domain\Identity\Port\UtilisateurRepository;
use App\Domain\Identity\ValueObject\RoleUtilisateur;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Charge les utilisateurs depuis le Domain (via le port) pour la sécurité Symfony.
 *
 * @implements UserProviderInterface<SecurityUser>
 */
final readonly class UtilisateurProvider implements UserProviderInterface
{
    public function __construct(private UtilisateurRepository $utilisateurs)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $utilisateur = $this->utilisateurs->parEmail($identifier);

        if (null === $utilisateur) {
            throw new UserNotFoundException();
        }

        return new SecurityUser(
            (string) $utilisateur->email,
            $utilisateur->motDePasseHache,
            array_map(static fn (RoleUtilisateur $r): string => $r->value, $utilisateur->roles),
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(sprintf('Utilisateur non supporté : %s.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class;
    }
}
