<?php

declare(strict_types=1);

namespace App\Domain\Identity\Exception;

use DomainException;

final class EmailInvalide extends DomainException
{
    public static function pour(string $valeur): self
    {
        return new self(sprintf('Adresse e-mail invalide : « %s ».', $valeur));
    }
}
