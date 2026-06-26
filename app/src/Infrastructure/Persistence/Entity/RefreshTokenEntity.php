<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;

/**
 * Entité concrète des refresh tokens (la classe du bundle est une
 * mapped-superclass) : hérite des champs et fournit la table refresh_tokens.
 */
#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
class RefreshTokenEntity extends BaseRefreshToken
{
}
