<?php

declare(strict_types=1);

namespace App\Domain\Shared\Port;

/**
 * Génère les identifiants des agrégats métier (UUIDv7, triables dans le temps).
 * Le Domain ne connaît que ce contrat ; l'implémentation vit en Infrastructure.
 */
interface IdGenerator
{
    public function generate(): string;
}
