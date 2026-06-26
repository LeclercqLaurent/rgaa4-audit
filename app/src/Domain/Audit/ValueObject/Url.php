<?php

declare(strict_types=1);

namespace App\Domain\Audit\ValueObject;

use App\Domain\Audit\Exception\UrlInvalide;
use Stringable;

/**
 * URL d'une page auditée. Normalisée (trim) et validée à la construction (http/https).
 */
final readonly class Url implements Stringable
{
    private const SCHEMES_AUTORISES = ['http', 'https'];

    public string $valeur;

    public function __construct(string $valeur)
    {
        $valeur = trim($valeur);
        $scheme = is_string($parsed = parse_url($valeur, PHP_URL_SCHEME)) ? $parsed : '';

        if (false === filter_var($valeur, FILTER_VALIDATE_URL) || !in_array($scheme, self::SCHEMES_AUTORISES, true)) {
            throw UrlInvalide::pour($valeur);
        }

        $this->valeur = $valeur;
    }

    public function __toString(): string
    {
        return $this->valeur;
    }
}
