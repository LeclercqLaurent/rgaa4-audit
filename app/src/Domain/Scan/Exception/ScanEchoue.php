<?php

declare(strict_types=1);

namespace App\Domain\Scan\Exception;

use RuntimeException;

/**
 * L'analyse automatique d'une page n'a pas pu aboutir (scanner indisponible,
 * page injoignable, sortie illisible…).
 */
final class ScanEchoue extends RuntimeException
{
    public static function pour(string $url, string $raison): self
    {
        return new self(sprintf('Échec du scan de « %s » : %s', $url, $raison));
    }
}
