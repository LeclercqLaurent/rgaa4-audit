<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

/**
 * Mesures de complexité d'une méthode/fonction analysée par phpx-complexity.
 */
final readonly class MethodeComplexite
{
    /**
     * @param array<string, int> $mesures clé de lentille → valeur mesurée
     */
    public function __construct(
        public string $fichier,
        public string $nom,
        public int $ligne,
        public array $mesures,
    ) {
    }

    public function reference(): string
    {
        return sprintf('%s::%s (l.%d)', $this->fichier, $this->nom, $this->ligne);
    }
}
