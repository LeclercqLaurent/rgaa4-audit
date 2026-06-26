<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Adapter;

use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\Port\ScanRepository;
use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\ResumeScan;
use App\Domain\Scan\ValueObject\StatutScan;
use App\Infrastructure\Persistence\Entity\ScanEntity;
use DateTimeImmutable;
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

    public function dernierResumeParProjet(): array
    {
        $rows = $this->em
            ->createQuery(sprintf('SELECT s.projetId AS projetId, s.statut AS statut, s.dateCreation AS date FROM %s s ORDER BY s.dateCreation DESC', ScanEntity::class))
            ->getArrayResult();

        $resumes = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $projetId = is_string($row['projetId'] ?? null) ? $row['projetId'] : '';
            if ('' === $projetId || isset($resumes[$projetId])) {
                continue;
            }

            $statut = is_string($row['statut'] ?? null) ? $row['statut'] : StatutScan::EnAttente->value;
            $date = ($row['date'] ?? null) instanceof DateTimeImmutable ? $row['date'] : new DateTimeImmutable();
            $resumes[$projetId] = new ResumeScan(StatutScan::from($statut), $date);
        }

        return $resumes;
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
