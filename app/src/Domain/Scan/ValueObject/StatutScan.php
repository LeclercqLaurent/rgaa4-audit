<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

/**
 * Cycle de vie d'un scan automatique (le scan multi-pages est asynchrone).
 */
enum StatutScan: string
{
    case EnAttente = 'pending';
    case EnCours = 'running';
    case Termine = 'done';
    case Echoue = 'failed';

    public function estTermine(): bool
    {
        return self::Termine === $this || self::Echoue === $this;
    }
}
