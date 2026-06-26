<?php

declare(strict_types=1);

namespace App\ApiResource\Audit\Input;

use App\Domain\Audit\ValueObject\Referentiel;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Charge utile de création d'un projet d'audit (POST /api/projets).
 * La cible est une URL (RGAA) ou un chemin de code (Complexité) — validée
 * selon le type côté handler.
 */
final class CreerProjetInput
{
    #[Assert\NotBlank(message: 'Le nom du projet est obligatoire.')]
    #[Assert\Length(max: 150)]
    public string $nom = '';

    #[Assert\NotBlank(message: 'Le client est obligatoire.')]
    #[Assert\Length(max: 150)]
    public string $client = '';

    #[Assert\NotBlank(message: 'Le type d\'audit est obligatoire.')]
    #[Assert\Choice(callback: [Referentiel::class, 'valeurs'], message: 'Type d\'audit inconnu.')]
    public string $type = '';

    #[Assert\NotBlank(message: 'La cible (URL ou chemin de code) est obligatoire.')]
    #[Assert\Length(max: 2048)]
    public string $cible = '';
}
