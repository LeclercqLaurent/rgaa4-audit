<?php

declare(strict_types=1);

namespace App\Domain\Referential\ValueObject;

enum NiveauWcag: string
{
    case A = 'A';
    case AA = 'AA';
    case AAA = 'AAA';
}
