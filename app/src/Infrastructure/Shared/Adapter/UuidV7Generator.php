<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Adapter;

use App\Domain\Shared\Port\IdGenerator;
use Symfony\Component\Uid\Uuid;

/**
 * Génère des UUIDv7 (RFC 9562) — triables chronologiquement.
 */
final readonly class UuidV7Generator implements IdGenerator
{
    public function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
