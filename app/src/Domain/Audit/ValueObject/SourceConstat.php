<?php

declare(strict_types=1);

namespace App\Domain\Audit\ValueObject;

/**
 * Origine d'un constat de conformité : proposé par l'automatique (axe-core) ou
 * établi/surchargé par l'auditeur.
 */
enum SourceConstat: string
{
    case Auto = 'auto';
    case Manuel = 'manuel';
}
