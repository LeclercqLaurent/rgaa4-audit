<?php

declare(strict_types=1);

namespace App\Domain\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\SourceConstat;

/**
 * Réduit l'ensemble des constats (auto + manuels) au constat « effectif » par
 * (page, critère) : le constat manuel de l'auditeur fait foi sur l'automatique.
 */
final readonly class ResolveurConstatsEffectifs
{
    /**
     * @param list<Constat> $constats
     *
     * @return list<Constat>
     */
    public function resoudre(array $constats): array
    {
        $effectifs = [];
        foreach ($constats as $constat) {
            $cle = $constat->pageUrl().'|'.$constat->critereNumero();
            if (!isset($effectifs[$cle]) || SourceConstat::Manuel === $constat->source()) {
                $effectifs[$cle] = $constat;
            }
        }

        return array_values($effectifs);
    }
}
