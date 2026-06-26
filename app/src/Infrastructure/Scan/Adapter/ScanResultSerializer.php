<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Adapter;

use App\Domain\Scan\ValueObject\NoeudAxe;
use App\Domain\Scan\ValueObject\ResultatAxe;
use App\Domain\Scan\ValueObject\ScanResult;

/**
 * Sérialise un ScanResult vers un tableau (colonne JSON), dans la même forme que
 * le JSON axe-core — ce qui permet de le réhydrater via AxeResultNormalizer.
 */
final readonly class ScanResultSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(ScanResult $resultat): array
    {
        return [
            'url' => $resultat->url,
            'timestamp' => $resultat->timestamp,
            'violations' => array_map($this->resultatToArray(...), $resultat->violations),
            'passes' => array_map($this->resultatToArray(...), $resultat->passes),
            'incomplete' => array_map($this->resultatToArray(...), $resultat->incomplete),
            'inapplicable' => array_map($this->resultatToArray(...), $resultat->inapplicable),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resultatToArray(ResultatAxe $r): array
    {
        return [
            'id' => $r->id,
            'impact' => $r->impact,
            'description' => $r->description,
            'help' => $r->help,
            'helpUrl' => $r->helpUrl,
            'tags' => $r->tags,
            'nodes' => array_map($this->noeudToArray(...), $r->noeuds),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function noeudToArray(NoeudAxe $n): array
    {
        return [
            'target' => $n->cibles,
            'html' => $n->html,
            'failureSummary' => $n->resumeEchec,
        ];
    }
}
