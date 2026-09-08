<?php

declare(strict_types=1);

/**
 * Confronte un rapport clover au plancher de couverture, couche par couche.
 *
 * Le plancher de 90 % porte sur le métier (Domain et Application) et non sur
 * la totalité de src/ : un adaptateur Doctrine ou un provider API Platform se
 * vérifie par un test d'API, pas en visant un pourcentage. Les couches hors
 * périmètre sont mesurées et affichées, jamais opposées à un seuil.
 *
 * Usage : php scripts/couverture.php app/var/clover.xml 90
 */

$fichier = $argv[1] ?? '';
$plancher = (float) ($argv[2] ?? 90);

/** Couches soumises au plancher. */
const COUCHES_GATEES = ['/src/Domain/', '/src/Application/'];

if (!is_file($fichier)) {
    fwrite(STDERR, sprintf("Rapport de couverture introuvable : %s\n", $fichier));
    exit(2);
}

$xml = simplexml_load_file($fichier);

if (false === $xml) {
    fwrite(STDERR, sprintf("Rapport de couverture illisible : %s\n", $fichier));
    exit(2);
}

/** @var array<string, array{0: int, 1: int}> $parCouche */
$parCouche = [];
$totalInstructions = 0;
$totalCouvert = 0;

foreach ($xml->xpath('//file') ?: [] as $noeud) {
    $metriques = $noeud->metrics;

    if (null === $metriques) {
        continue;
    }

    $chemin = (string) $noeud['name'];
    $instructions = (int) $metriques['statements'];
    $couvert = (int) $metriques['coveredstatements'];

    if (!preg_match('#/(src/[^/]+)/#', $chemin, $trouve)) {
        continue;
    }

    $couche = $trouve[1];
    $parCouche[$couche] ??= [0, 0];
    $parCouche[$couche][0] += $couvert;
    $parCouche[$couche][1] += $instructions;
    $totalCouvert += $couvert;
    $totalInstructions += $instructions;
}

if (0 === $totalInstructions) {
    fwrite(STDERR, "Aucune instruction mesurée.\n");
    exit(2);
}

$gateCouvert = 0;
$gateInstructions = 0;
ksort($parCouche);

foreach ($parCouche as $couche => [$couvert, $instructions]) {
    $gatee = in_array('/'.$couche.'/', COUCHES_GATEES, true);

    if ($gatee) {
        $gateCouvert += $couvert;
        $gateInstructions += $instructions;
    }

    printf(
        "   %-22s %5.1f %% (%4d/%4d)%s\n",
        $couche,
        0 === $instructions ? 0.0 : 100 * $couvert / $instructions,
        $couvert,
        $instructions,
        $gatee ? '  ← soumis au plancher' : ''
    );
}

$global = 100 * $totalCouvert / $totalInstructions;
$metier = 0 === $gateInstructions ? 0.0 : 100 * $gateCouvert / $gateInstructions;

printf("   %-22s %5.1f %% (%4d/%4d)\n", 'TOTAL src/', $global, $totalCouvert, $totalInstructions);
printf("\n   Métier (Domain + Application) : %.1f %%, plancher %.0f %%\n", $metier, $plancher);

exit($metier + 0.05 >= $plancher ? 0 : 1);
