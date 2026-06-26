<?php

declare(strict_types=1);

namespace App\ApiResource\Scan;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Audit\ProjetResource;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\State\Scan\PlanifierScanProcessor;
use App\State\Scan\ScanProvider;

#[ApiResource(
    shortName: 'Scan',
    operations: [
        new Get(uriTemplate: '/scans/{id}'),
        new GetCollection(
            uriTemplate: '/projets/{projetId}/scans',
            uriVariables: [
                'projetId' => new Link(fromClass: ProjetResource::class, identifiers: ['id'], parameterName: 'projetId'),
            ],
        ),
        new Post(
            uriTemplate: '/projets/{projetId}/scans',
            uriVariables: [
                'projetId' => new Link(fromClass: ProjetResource::class, identifiers: ['id'], parameterName: 'projetId'),
            ],
            input: false,
            processor: PlanifierScanProcessor::class,
            exceptionToStatus: [ProjetIntrouvable::class => 404],
        ),
    ],
    provider: ScanProvider::class,
)]
final class ScanResource
{
    /**
     * @param list<ScanPageResumeResource> $pages
     */
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $id,
        public string $projetId,
        public string $statut,
        public string $dateCreation,
        public ?string $dateFin,
        public ?string $erreur,
        public array $pages,
    ) {
    }
}
