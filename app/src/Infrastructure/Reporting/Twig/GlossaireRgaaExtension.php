<?php

declare(strict_types=1);

namespace App\Infrastructure\Reporting\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Les intitulés officiels du RGAA renvoient au glossaire en syntaxe markdown,
 * par exemple « Chaque [image porteuse d'information](#image-porteuse-d-information)
 * a-t-elle une alternative ? ». Le référentiel est stocké tel que la DINUM le
 * publie, sans retouche : c'est au rendu de décider quoi en faire.
 *
 * Un rapport PDF n'a pas de glossaire à cibler : le lien y devient du bruit
 * illisible. Ce filtre ne garde que le libellé.
 */
final class GlossaireRgaaExtension extends AbstractExtension
{
    /**
     * Capture `[libellé](cible)` sans traverser un `]` ou un `)`, afin qu'une
     * phrase portant plusieurs liens soit traitée lien par lien.
     */
    private const LIEN_MARKDOWN = '/\[([^\]]*)\]\([^)]*\)/u';

    public function getFilters(): array
    {
        return [
            new TwigFilter('sans_glossaire', $this->sansGlossaire(...)),
        ];
    }

    /**
     * Remplace chaque lien markdown par son seul libellé.
     */
    public function sansGlossaire(string $texte): string
    {
        return preg_replace(self::LIEN_MARKDOWN, '$1', $texte) ?? $texte;
    }
}
