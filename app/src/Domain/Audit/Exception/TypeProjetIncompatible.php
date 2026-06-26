<?php

declare(strict_types=1);

namespace App\Domain\Audit\Exception;

use DomainException;

/**
 * Opération incompatible avec le type d'audit du projet (ex. analyse de
 * complexité demandée sur un projet RGAA).
 */
final class TypeProjetIncompatible extends DomainException
{
    public static function pour(string $attendu): self
    {
        return new self(sprintf('Opération réservée aux projets de type « %s ».', $attendu));
    }
}
