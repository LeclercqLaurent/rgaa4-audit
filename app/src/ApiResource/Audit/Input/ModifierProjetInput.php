<?php

declare(strict_types=1);

namespace App\ApiResource\Audit\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Charge utile de modification d'un projet d'audit (PATCH /api/projets/{id}).
 * Le type d'audit n'est pas modifiable ; la cible est validée selon le type
 * côté handler.
 */
final class ModifierProjetInput
{
    #[Assert\NotBlank(message: 'Le nom du projet est obligatoire.')]
    #[Assert\Length(max: 150)]
    public string $nom = '';

    #[Assert\NotBlank(message: 'Le client est obligatoire.')]
    #[Assert\Length(max: 150)]
    public string $client = '';

    #[Assert\NotBlank(message: 'La cible est obligatoire.')]
    #[Assert\Length(max: 2048)]
    public string $cible = '';
}
