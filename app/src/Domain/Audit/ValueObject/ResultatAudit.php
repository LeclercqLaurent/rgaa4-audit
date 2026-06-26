<?php

declare(strict_types=1);

namespace App\Domain\Audit\ValueObject;

/**
 * Issue du déclenchement d'un audit : soit synchrone (constats produits
 * immédiatement, ex. complexité), soit asynchrone (scan planifié, ex. RGAA).
 */
final readonly class ResultatAudit
{
    private function __construct(
        public bool $synchrone,
        public ?int $constatsGeneres,
    ) {
    }

    public static function synchrone(int $constatsGeneres): self
    {
        return new self(true, $constatsGeneres);
    }

    public static function asynchrone(): self
    {
        return new self(false, null);
    }
}
