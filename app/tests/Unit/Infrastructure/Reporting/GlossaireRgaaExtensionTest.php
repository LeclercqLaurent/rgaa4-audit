<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Reporting;

use App\Infrastructure\Reporting\Twig\GlossaireRgaaExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Les intitulés viennent du référentiel officiel, où les renvois au glossaire
 * sont écrits en markdown. Les valeurs attendues sont tirées de ces intitulés
 * réels, pas de la sortie du filtre.
 */
final class GlossaireRgaaExtensionTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function intitules(): iterable
    {
        yield 'critère 1.1, deux renvois au glossaire' => [
            'Chaque [image porteuse d’information](#image-porteuse-d-information) a-t-elle une [alternative textuelle](#alternative-textuelle-image) ?',
            'Chaque image porteuse d’information a-t-elle une alternative textuelle ?',
        ];

        yield 'critère 13.3, un seul renvoi' => [
            'Dans chaque page web, chaque document bureautique en téléchargement possède-t-il, si nécessaire, une [version accessible](#version-accessible-pour-un-document-en-telechargement) (hors cas particuliers) ?',
            'Dans chaque page web, chaque document bureautique en téléchargement possède-t-il, si nécessaire, une version accessible (hors cas particuliers) ?',
        ];

        yield 'intitulé sans renvoi, laissé intact' => [
            'Chaque page web est-elle utilisable au clavier ?',
            'Chaque page web est-elle utilisable au clavier ?',
        ];

        yield 'crochets isolés, qui ne forment pas un lien' => [
            'L’attribut [lang] est-il présent ?',
            'L’attribut [lang] est-il présent ?',
        ];

        yield 'chaîne vide' => ['', ''];
    }

    #[DataProvider('intitules')]
    public function testLeRenvoiAuGlossaireEstReduitASonLibelle(string $intitule, string $attendu): void
    {
        self::assertSame($attendu, (new GlossaireRgaaExtension())->sansGlossaire($intitule));
    }

    public function testLeFiltreEstExposeAuxGabarits(): void
    {
        $noms = array_map(
            static fn (object $filtre): string => (string) $filtre->getName(),
            (new GlossaireRgaaExtension())->getFilters(),
        );

        self::assertContains('sans_glossaire', $noms);
    }
}
