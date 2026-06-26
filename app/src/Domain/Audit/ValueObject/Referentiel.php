<?php

declare(strict_types=1);

namespace App\Domain\Audit\ValueObject;

/**
 * Référentiel d'audit dont relève un constat. Permet de faire cohabiter
 * plusieurs moteurs sur un même projet (RGAA, complexité PHP, …).
 */
enum Referentiel: string
{
    case Rgaa = 'rgaa';
    case ComplexitePhp = 'complexite_php';

    public function libelle(): string
    {
        return match ($this) {
            self::Rgaa => 'RGAA 4',
            self::ComplexitePhp => 'Complexité PHP',
        };
    }

    /**
     * @return list<string>
     */
    public static function valeurs(): array
    {
        return array_map(static fn (self $r): string => $r->value, self::cases());
    }
}
