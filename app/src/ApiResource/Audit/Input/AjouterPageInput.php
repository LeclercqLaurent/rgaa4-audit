<?php

declare(strict_types=1);

namespace App\ApiResource\Audit\Input;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Charge utile d'ajout d'une page à l'échantillon (POST /api/projets/{projetId}/pages).
 */
final class AjouterPageInput
{
    #[Assert\NotBlank(message: 'L\'URL de la page est obligatoire.')]
    #[Assert\Url(protocols: ['http', 'https'], message: 'L\'URL de la page doit être une URL http(s) valide.')]
    public string $url = '';

    #[Assert\NotBlank(message: 'Le titre de la page est obligatoire.')]
    #[Assert\Length(max: 255)]
    public string $titre = '';
}
