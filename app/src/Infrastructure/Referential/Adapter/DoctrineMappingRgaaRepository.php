<?php

declare(strict_types=1);

namespace App\Infrastructure\Referential\Adapter;

use App\Domain\Referential\Port\MappingRgaaRepository;
use App\Infrastructure\Persistence\Entity\MappingAxeEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineMappingRgaaRepository implements MappingRgaaRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function associations(): array
    {
        $associations = [];

        foreach ($this->em->getRepository(MappingAxeEntity::class)->findAll() as $entity) {
            $associations[$entity->getAxeTag()][] = $entity->getCritereNumero();
        }

        return $associations;
    }
}
