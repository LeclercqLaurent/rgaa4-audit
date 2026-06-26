<?php

declare(strict_types=1);

namespace App\Domain\Identity\ValueObject;

use App\Domain\Identity\Exception\EmailInvalide;
use Stringable;

final readonly class Email implements Stringable
{
    public string $valeur;

    public function __construct(string $valeur)
    {
        $valeur = mb_strtolower(trim($valeur));

        if (false === filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
            throw EmailInvalide::pour($valeur);
        }

        $this->valeur = $valeur;
    }

    public function __toString(): string
    {
        return $this->valeur;
    }
}
