<?php

declare(strict_types=1);

namespace App\Infrastructure\Referential\Command;

use App\Infrastructure\Persistence\Entity\CritereEntity;
use App\Infrastructure\Persistence\Entity\TestEntity;
use App\Infrastructure\Persistence\Entity\ThematiqueEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'app:referential:load',
    description: 'Charge le référentiel RGAA (thématiques/critères/tests) en base depuis les fixtures YAML.',
)]
final class LoadReferentialCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $criteres = $this->parse('fixtures/rgaa/criteres.yaml');
        $wcagIndex = $this->buildWcagIndex($this->parse('fixtures/rgaa/mapping-rgaa-wcag.yaml'));

        $this->truncate();
        $thematiques = $this->rows($criteres['thematiques'] ?? null);
        [$nbCriteres, $nbTests] = $this->persistThematiques($thematiques, $wcagIndex);
        $this->em->flush();

        $io->success(\sprintf(
            'Référentiel chargé : %d thématiques, %d critères, %d tests.',
            \count($thematiques),
            $nbCriteres,
            $nbTests,
        ));

        return Command::SUCCESS;
    }

    /**
     * @param list<array<string, mixed>>                                                          $thematiques
     * @param array<string, list<array{sc: string, intitule: string, niveau: string, axe_tag: string}>> $wcagIndex
     *
     * @return array{0: int, 1: int} [nombre de critères, nombre de tests]
     */
    private function persistThematiques(array $thematiques, array $wcagIndex): array
    {
        $nbCriteres = 0;
        $nbTests = 0;
        foreach ($thematiques as $data) {
            $thematique = new ThematiqueEntity($this->int($data['numero'] ?? null), $this->str($data['nom'] ?? null));
            $this->em->persist($thematique);
            foreach ($this->rows($data['criteres'] ?? null) as $critereData) {
                $nbTests += $this->persistCritere($thematique, $critereData, $wcagIndex);
                ++$nbCriteres;
            }
        }

        return [$nbCriteres, $nbTests];
    }

    /**
     * @param array<string, mixed>                                                                $data
     * @param array<string, list<array{sc: string, intitule: string, niveau: string, axe_tag: string}>> $wcagIndex
     *
     * @return int nombre de tests persistés pour ce critère
     */
    private function persistCritere(ThematiqueEntity $thematique, array $data, array $wcagIndex): int
    {
        $numero = $this->str($data['numero'] ?? null);
        $critere = new CritereEntity(
            $numero,
            $thematique,
            $this->str($data['intitule'] ?? null),
            $wcagIndex[$numero] ?? [],
            $this->strings($data['techniques'] ?? null),
        );
        $this->em->persist($critere);
        $thematique->addCritere($critere);

        $nbTests = 0;
        foreach ($this->rows($data['tests'] ?? null) as $testData) {
            $test = new TestEntity($this->str($testData['numero'] ?? null), $critere, $this->strings($testData['enonce'] ?? null));
            $this->em->persist($test);
            $critere->addTest($test);
            ++$nbTests;
        }

        return $nbTests;
    }

    /**
     * @param array<string, mixed> $mapping
     *
     * @return array<string, list<array{sc: string, intitule: string, niveau: string, axe_tag: string}>>
     */
    private function buildWcagIndex(array $mapping): array
    {
        $index = [];
        foreach ($this->rows($mapping['criteres'] ?? null) as $critere) {
            $references = [];
            foreach ($this->rows($critere['wcag'] ?? null) as $reference) {
                $references[] = [
                    'sc' => $this->str($reference['sc'] ?? null),
                    'intitule' => $this->str($reference['intitule'] ?? null),
                    'niveau' => $this->str($reference['niveau'] ?? null),
                    'axe_tag' => $this->str($reference['axe_tag'] ?? null),
                ];
            }
            $index[$this->str($critere['rgaa'] ?? null)] = $references;
        }

        return $index;
    }

    private function truncate(): void
    {
        $connection = $this->em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['rgaa_test', 'rgaa_critere', 'rgaa_thematique'] as $table) {
            $connection->executeStatement('TRUNCATE TABLE ' . $table);
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @return array<string, mixed>
     */
    private function parse(string $relativePath): array
    {
        $parsed = Yaml::parseFile($this->projectDir . '/' . $relativePath);

        return \is_array($parsed) ? $parsed : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $rows = [];
        foreach ($value as $row) {
            if (\is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function str(mixed $value): string
    {
        return \is_scalar($value) ? (string) $value : '';
    }

    private function int(mixed $value): int
    {
        return \is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @return list<string>
     */
    private function strings(mixed $value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        return array_values(array_map(strval(...), array_filter($value, static fn (mixed $v): bool => \is_scalar($v))));
    }
}
