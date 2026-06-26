<?php

declare(strict_types=1);

namespace App\Domain\Audit\Exception;

use DomainException;

/**
 * URL fournie pour un projet ou une page d'échantillon non exploitable.
 */
final class UrlInvalide extends DomainException
{
    public static function pour(string $valeur): self
    {
        return new self(sprintf('URL invalide : « %s » (attendu http(s)://…).', $valeur));
    }
}
