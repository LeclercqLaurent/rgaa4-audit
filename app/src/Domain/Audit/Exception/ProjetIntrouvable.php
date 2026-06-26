<?php

declare(strict_types=1);

namespace App\Domain\Audit\Exception;

use DomainException;

/**
 * Aucun projet d'audit ne correspond à l'identifiant demandé.
 */
final class ProjetIntrouvable extends DomainException
{
    public static function pour(string $id): self
    {
        return new self(sprintf('Projet d\'audit introuvable : %s.', $id));
    }
}
