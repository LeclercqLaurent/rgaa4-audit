<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Adapter;

use App\Domain\Scan\ValueObject\NoeudAxe;
use App\Domain\Scan\ValueObject\ResultatAxe;
use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\ScanResult;
use UnexpectedValueException;

/**
 * Normalise le JSON axe-core brut (issu du scanner Node) en Value Objects du
 * Domain. On ne fait confiance qu'au contrat JSON, jamais à la mécanique Node.
 */
final readonly class AxeResultNormalizer
{
    public function fromJson(string $json): ScanResult
    {
        /** @var mixed $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new UnexpectedValueException('Sortie axe-core inattendue (objet JSON attendu).');
        }

        return $this->fromArray($data);
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function fromArray(array $data): ScanResult
    {
        return new ScanResult(
            $this->str($data['url'] ?? ''),
            $this->str($data['timestamp'] ?? ''),
            $this->resultats($data['violations'] ?? null),
            $this->resultats($data['passes'] ?? null),
            $this->resultats($data['incomplete'] ?? null),
            $this->resultats($data['inapplicable'] ?? null),
        );
    }

    public function resultatPage(string $url, string $json): ResultatPage
    {
        return new ResultatPage($url, $this->fromJson($json));
    }

    /**
     * @return list<ResultatAxe>
     */
    private function resultats(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $resultats = array_map($this->resultat(...), array_filter($raw, is_array(...)));

        return array_values($resultats);
    }

    /**
     * @param array<array-key, mixed> $r
     */
    private function resultat(array $r): ResultatAxe
    {
        return new ResultatAxe(
            $this->str($r['id'] ?? ''),
            $this->nullableStr($r['impact'] ?? null),
            $this->str($r['description'] ?? ''),
            $this->str($r['help'] ?? ''),
            $this->str($r['helpUrl'] ?? ''),
            $this->strings($r['tags'] ?? null),
            $this->noeuds($r['nodes'] ?? null),
        );
    }

    /**
     * @return list<NoeudAxe>
     */
    private function noeuds(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        return array_values(array_map($this->noeud(...), array_filter($raw, is_array(...))));
    }

    /**
     * @param array<array-key, mixed> $n
     */
    private function noeud(array $n): NoeudAxe
    {
        return new NoeudAxe(
            $this->strings($n['target'] ?? null),
            $this->str($n['html'] ?? ''),
            $this->nullableStr($n['failureSummary'] ?? null),
        );
    }

    private function str(mixed $v): string
    {
        return is_string($v) ? $v : (is_scalar($v) ? (string) $v : '');
    }

    private function nullableStr(mixed $v): ?string
    {
        return is_string($v) && '' !== $v ? $v : null;
    }

    /**
     * @return list<string>
     */
    private function strings(mixed $v): array
    {
        if (!is_array($v)) {
            return [];
        }

        $strings = array_filter(array_map($this->str(...), $v), static fn (string $s): bool => '' !== $s);

        return array_values($strings);
    }
}
