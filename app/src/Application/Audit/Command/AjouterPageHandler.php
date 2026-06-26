<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\Entity\Page;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Port\ProjetRepository;
use App\Domain\Audit\ValueObject\Url;
use App\Domain\Shared\Port\IdGenerator;

final readonly class AjouterPageHandler
{
    public function __construct(
        private ProjetRepository $projets,
        private IdGenerator $ids,
    ) {
    }

    /**
     * @return string Identifiant de la page ajoutée
     */
    public function __invoke(AjouterPage $command): string
    {
        $projet = $this->projets->get($command->projetId);

        if (null === $projet) {
            throw ProjetIntrouvable::pour($command->projetId);
        }

        $page = new Page($this->ids->generate(), new Url($command->url), $command->titre);
        $projet->ajouterPage($page);
        $this->projets->save($projet);

        return $page->id;
    }
}
