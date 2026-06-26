<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Adapter;

use App\Domain\Scan\Exception\ScanEchoue;
use App\Domain\Scan\Port\PageScanner;
use App\Domain\Scan\ValueObject\ScanResult;
use JsonException;
use Symfony\Component\Process\Process;
use UnexpectedValueException;

/**
 * Lance le scanner Node (Puppeteer + axe-core) en CLI via Process.
 *
 * La commande de base est configurable (`SCANNER_COMMAND`) pour isoler la
 * frontière PHP ↔ Node : en dev, www invoque le conteneur scanner
 * (`docker exec scanner_rgaa node /scanner/scan.js`). Passer à un microservice
 * HTTP n'impacterait que cet adapter, pas le Domain.
 */
final readonly class ProcessPageScanner implements PageScanner
{
    /**
     * @param list<string> $baseCommand
     */
    public function __construct(
        private array $baseCommand,
        private int $timeoutSeconds,
        private AxeResultNormalizer $normalizer,
    ) {
    }

    public function scan(string $url): ScanResult
    {
        $process = new Process([...$this->baseCommand, $url]);
        $process->setTimeout((float) $this->timeoutSeconds);
        $process->run();

        if (!$process->isSuccessful()) {
            $raison = trim($process->getErrorOutput());

            throw ScanEchoue::pour($url, '' !== $raison ? $raison : 'code de sortie '.(string) $process->getExitCode());
        }

        try {
            return $this->normalizer->fromJson($process->getOutput());
        } catch (JsonException|UnexpectedValueException $e) {
            throw ScanEchoue::pour($url, $e->getMessage());
        }
    }
}
