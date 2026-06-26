<?php

declare(strict_types=1);

namespace App\ApiResource\Audit;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Audit\Input\AjouterPageInput;
use App\ApiResource\Audit\Input\CreerProjetInput;
use App\ApiResource\Audit\Input\ModifierProjetInput;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\State\Audit\ModifierProjetProcessor;
use App\State\Audit\PageProcessor;
use App\State\Audit\ProjetProcessor;
use App\State\Audit\ProjetProvider;
use App\State\Audit\SupprimerProjetProcessor;

#[ApiResource(
    shortName: 'Projet',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            input: CreerProjetInput::class,
            processor: ProjetProcessor::class,
        ),
        new Patch(
            input: ModifierProjetInput::class,
            processor: ModifierProjetProcessor::class,
            exceptionToStatus: [ProjetIntrouvable::class => 404],
        ),
        new Delete(
            processor: SupprimerProjetProcessor::class,
            exceptionToStatus: [ProjetIntrouvable::class => 404],
        ),
        new Post(
            uriTemplate: '/projets/{id}/pages',
            input: AjouterPageInput::class,
            processor: PageProcessor::class,
            exceptionToStatus: [ProjetIntrouvable::class => 404],
        ),
    ],
    provider: ProjetProvider::class,
)]
final class ProjetResource
{
    /**
     * @param list<PageResource> $pages
     */
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $id,
        public string $nom,
        public string $client,
        public string $urlReference,
        public string $dateCreation,
        public array $pages,
    ) {
    }
}
