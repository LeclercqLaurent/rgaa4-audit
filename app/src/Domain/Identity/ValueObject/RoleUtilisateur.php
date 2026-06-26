<?php

declare(strict_types=1);

namespace App\Domain\Identity\ValueObject;

/**
 * Rôle métier d'un utilisateur. La valeur est le rôle Symfony correspondant.
 */
enum RoleUtilisateur: string
{
    case Auditeur = 'ROLE_AUDITEUR';
    case Client = 'ROLE_CLIENT';
}
