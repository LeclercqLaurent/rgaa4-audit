<?php

declare(strict_types=1);

namespace App\ApiResource\Audit\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Charge utile de création d'un projet d'audit (POST /api/projets).
 */
final class CreerProjetInput
{
    #[Assert\NotBlank(message: 'Le nom du projet est obligatoire.')]
    #[Assert\Length(max: 150)]
    public string $nom = '';

    #[Assert\NotBlank(message: 'Le client est obligatoire.')]
    #[Assert\Length(max: 150)]
    public string $client = '';

    #[Assert\NotBlank(message: 'L\'URL de référence est obligatoire.')]
    #[Assert\Url(protocols: ['http', 'https'], message: 'L\'URL de référence doit être une URL http(s) valide.')]
    public string $urlReference = '';
}
