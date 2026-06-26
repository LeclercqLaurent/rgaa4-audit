<?php

declare(strict_types=1);

// Coding standards du socle : PSR-12 + declare(strict_types) + ligne vide en fin
// de fichier + imports triés. Voir CLAUDE.md (socle « Coding Standards »).

$dirs = array_filter(
    [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/tools'],
    'is_dir',
);

$finder = (new PhpCsFixer\Finder())->in($dirs);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'blank_line_after_opening_tag' => true,
        'single_blank_line_at_eof' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_functions' => false],
    ])
    ->setFinder($finder);
