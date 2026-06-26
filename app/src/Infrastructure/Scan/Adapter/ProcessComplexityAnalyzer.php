<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Adapter;

use App\Domain\Scan\Port\AnalyseurCode;
use App\Domain\Scan\ValueObject\MethodeComplexite;
use App\Domain\Scan\ValueObject\ResultatComplexite;
use RuntimeException;
use Symfony\Component\Process\Process;
use UnexpectedValueException;

/**
 * Exécute phpx-complexity (CLI PHP) sur un dossier et normalise sa sortie JSON.
 *
 * Contrairement au scanner Node (Puppeteer), ce moteur est du PHP : il s'exécute
 * directement dans le conteneur www, sans Docker. La commande de base est
 * configurable (`COMPLEXITY_COMMAND`).
 */
final readonly class ProcessComplexityAnalyzer implements AnalyseurCode
{
    /**
     * @param list<string> $baseCommand
     */
    public function __construct(
        private array $baseCommand,
        private int $timeoutSeconds,
    ) {
    }

    public function analyser(string $chemin): ResultatComplexite
    {
        // phpx-complexity résout la racine projet (composer.json) depuis le cwd
        // et attend un chemin relatif : on exécute donc depuis le dossier analysé.
        $process = new Process([...$this->baseCommand, '.', '--json'], $chemin);
        $process->setTimeout((float) $this->timeoutSeconds);
        $process->run();

        if (!$process->isSuccessful()) {
            $raison = trim($process->getErrorOutput());

            throw new RuntimeException(sprintf('Analyse de complexité échouée : %s', '' !== $raison ? $raison : 'code '.(string) $process->getExitCode()));
        }

        /** @var mixed $data */
        $data = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new UnexpectedValueException('Sortie phpx-complexity inattendue (objet JSON attendu).');
        }

        $seuils = $this->seuils($data['lenses'] ?? null);

        return new ResultatComplexite($this->methodes($data['methods'] ?? null, array_keys($seuils)), $seuils);
    }

    /**
     * @return array<string, int>
     */
    private function seuils(mixed $lenses): array
    {
        if (!is_array($lenses)) {
            return [];
        }

        $seuils = [];
        foreach ($lenses as $cle => $lentille) {
            if (is_string($cle) && is_array($lentille) && is_int($lentille['threshold'] ?? null)) {
                $seuils[$cle] = $lentille['threshold'];
            }
        }

        return $seuils;
    }

    /**
     * @param list<string> $lentilles
     *
     * @return list<MethodeComplexite>
     */
    private function methodes(mixed $methods, array $lentilles): array
    {
        if (!is_array($methods)) {
            return [];
        }

        return array_values(array_map(
            fn (array $m): MethodeComplexite => $this->methode($m, $lentilles),
            array_filter($methods, is_array(...)),
        ));
    }

    /**
     * @param array<array-key, mixed> $m
     * @param list<string>            $lentilles
     */
    private function methode(array $m, array $lentilles): MethodeComplexite
    {
        $metrics = is_array($m['metrics'] ?? null) ? $m['metrics'] : [];
        $mesures = [];
        foreach ($lentilles as $lentille) {
            $mesures[$lentille] = is_int($metrics[$lentille] ?? null) ? $metrics[$lentille] : 0;
        }

        return new MethodeComplexite(
            is_string($m['file'] ?? null) ? $m['file'] : '',
            is_string($m['name'] ?? null) ? $m['name'] : '',
            is_int($m['line'] ?? null) ? $m['line'] : 0,
            $mesures,
        );
    }
}
