<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Adapter;

use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\Port\ScanRepository;
use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\StatutScan;
use App\Infrastructure\Persistence\Entity\ScanEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineScanRepository implements ScanRepository
{
    public function __construct(
        private EntityManagerInterface $em,
        private ScanResultSerializer $serializer,
        private AxeResultNormalizer $normalizer,
    ) {
    }

    public function save(Scan $scan): void
    {
        $entity = $this->em->getRepository(ScanEntity::class)->find($scan->id());

        if (!$entity instanceof ScanEntity) {
            $entity = new ScanEntity($scan->id(), $scan->projetId(), $scan->statut()->value, $scan->dateCreation());
            $this->em->persist($entity);
        }

        $entity->maj($scan->statut()->value, $this->resultatsToArray($scan), $scan->dateFin(), $scan->erreur());
        $this->em->flush();
    }

    public function get(string $id): ?Scan
    {
        $entity = $this->em->getRepository(ScanEntity::class)->find($id);

        return $entity instanceof ScanEntity ? $this->toDomain($entity) : null;
    }

    public function findByProjet(string $projetId): array
    {
        $entities = $this->em->getRepository(ScanEntity::class)->findBy(['projetId' => $projetId], ['dateCreation' => 'DESC']);

        return array_map($this->toDomain(...), $entities);
    }

    public function supprimerPourProjet(string $projetId): void
    {
        $this->em->createQuery(sprintf('DELETE FROM %s s WHERE s.projetId = :projet', ScanEntity::class))
            ->setParameter('projet', $projetId)
            ->execute();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resultatsToArray(Scan $scan): array
    {
        return array_map(
            fn (ResultatPage $rp): array => ['url' => $rp->url, 'resultat' => $this->serializer->toArray($rp->resultat)],
            $scan->resultats(),
        );
    }

    private function toDomain(ScanEntity $entity): Scan
    {
        return Scan::reconstituer(
            $entity->getId(),
            $entity->getProjetId(),
            $entity->getDateCreation(),
            StatutScan::from($entity->getStatut()),
            $entity->getDateFin(),
            $entity->getErreur(),
            array_map($this->entryToResultatPage(...), $entity->getResultats()),
        );
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function entryToResultatPage(array $entry): ResultatPage
    {
        $url = is_string($entry['url'] ?? null) ? $entry['url'] : '';
        $resultat = is_array($entry['resultat'] ?? null) ? $entry['resultat'] : [];

        return new ResultatPage($url, $this->normalizer->fromArray($resultat));
    }
}
