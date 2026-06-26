<?php

declare(strict_types=1);

namespace App\Infrastructure\Referential\Command;

use App\Infrastructure\Persistence\Entity\MappingAxeEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

/**
 * Charge la table de mapping axe-core → RGAA depuis mapping-wcag-rgaa.yaml.
 */
#[AsCommand(name: 'app:mapping:load', description: 'Charge la table de mapping axe-core → critères RGAA.')]
final class LoadMappingCommand extends Command
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
        $this->truncate();

        $nb = 0;
        foreach ($this->successCriteria() as $sc) {
            $nb += $this->persistSuccessCriterion($sc);
        }
        $this->em->flush();

        $io->success(sprintf('Mapping axe → RGAA chargé : %d associations.', $nb));

        return Command::SUCCESS;
    }

    /**
     * @param array<array-key, mixed> $sc
     */
    private function persistSuccessCriterion(array $sc): int
    {
        $axeTag = is_string($sc['axe_tag'] ?? null) ? $sc['axe_tag'] : '';
        $criteres = is_array($sc['criteres_rgaa'] ?? null) ? $sc['criteres_rgaa'] : [];

        if ('' === $axeTag) {
            return 0;
        }

        $nb = 0;
        foreach ($criteres as $critere) {
            if (is_string($critere) || is_int($critere)) {
                $this->em->persist(new MappingAxeEntity($axeTag, (string) $critere));
                ++$nb;
            }
        }

        return $nb;
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function successCriteria(): array
    {
        $parsed = Yaml::parseFile($this->projectDir.'/fixtures/rgaa/mapping-wcag-rgaa.yaml');
        $rows = is_array($parsed) && is_array($parsed['criteres_succes'] ?? null) ? $parsed['criteres_succes'] : [];

        return array_values(array_filter($rows, is_array(...)));
    }

    private function truncate(): void
    {
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE rgaa_mapping_axe');
    }
}
