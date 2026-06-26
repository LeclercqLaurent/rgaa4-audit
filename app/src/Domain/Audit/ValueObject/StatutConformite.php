<?php

declare(strict_types=1);

namespace App\Domain\Audit\ValueObject;

/**
 * Statut de conformité d'un critère RGAA pour une page donnée.
 *
 * `non_teste` est l'état par défaut (ni l'automatique ni l'auditeur ne se sont
 * encore prononcés). `non_applicable` exclut le critère du calcul du taux.
 */
enum StatutConformite: string
{
    case Conforme = 'conforme';
    case NonConforme = 'non_conforme';
    case NonApplicable = 'non_applicable';
    case NonTeste = 'non_teste';

    public function compteDansLeTaux(): bool
    {
        return self::Conforme === $this || self::NonConforme === $this;
    }
}
