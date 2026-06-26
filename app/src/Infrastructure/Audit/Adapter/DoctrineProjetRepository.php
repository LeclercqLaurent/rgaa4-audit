<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Adapter;

use App\Domain\Audit\Entity\Page;
use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\Port\ProjetRepository;
use App\Domain\Audit\ValueObject\Url;
use App\Infrastructure\Persistence\Entity\PageEntity;
use App\Infrastructure\Persistence\Entity\ProjetEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProjetRepository implements ProjetRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Projet $projet): void
    {
        $entity = $this->em->getRepository(ProjetEntity::class)->find($projet->id());

        if (!$entity instanceof ProjetEntity) {
            $entity = new ProjetEntity(
                $projet->id(),
                $projet->nom(),
                $projet->client(),
                (string) $projet->urlReference(),
                $projet->dateCreation(),
            );
            $this->em->persist($entity);
        }

        $this->synchroniserPages($projet, $entity);
        $this->em->flush();
    }

    public function findAll(): array
    {
        $entities = $this->em->getRepository(ProjetEntity::class)->findBy([], ['dateCreation' => 'DESC']);

        return array_map($this->toDomain(...), $entities);
    }

    public function get(string $id): ?Projet
    {
        $entity = $this->em->getRepository(ProjetEntity::class)->find($id);

        return $entity instanceof ProjetEntity ? $this->toDomain($entity) : null;
    }

    private function synchroniserPages(Projet $projet, ProjetEntity $entity): void
    {
        $existants = array_map(static fn (PageEntity $p): string => $p->getId(), $entity->getPages()->toArray());

        foreach ($projet->pages() as $page) {
            if (!in_array($page->id, $existants, true)) {
                $pageEntity = new PageEntity($page->id, (string) $page->url, $page->titre, $entity);
                $this->em->persist($pageEntity);
                $entity->addPage($pageEntity);
            }
        }
    }

    private function toDomain(ProjetEntity $entity): Projet
    {
        $pages = array_map(
            static fn (PageEntity $p): Page => new Page($p->getId(), new Url($p->getUrl()), $p->getTitre()),
            $entity->getPages()->toArray(),
        );

        return new Projet(
            $entity->getId(),
            $entity->getNom(),
            $entity->getClient(),
            new Url($entity->getUrlReference()),
            $entity->getDateCreation(),
            array_values($pages),
        );
    }
}
