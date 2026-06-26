<?php

declare(strict_types=1);

namespace App\Application\Scan\Command;

/**
 * Message asynchrone (Messenger) déclenchant l'exécution effective d'un scan
 * déjà planifié (statut « en attente »).
 */
final readonly class LancerScan
{
    public function __construct(public string $scanId)
    {
    }
}
