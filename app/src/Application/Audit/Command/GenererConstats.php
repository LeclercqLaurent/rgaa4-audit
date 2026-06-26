<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

/**
 * Message déclenchant la (re)génération des constats automatiques d'un projet à
 * partir de son dernier scan terminé.
 */
final readonly class GenererConstats
{
    public function __construct(public string $projetId)
    {
    }
}
