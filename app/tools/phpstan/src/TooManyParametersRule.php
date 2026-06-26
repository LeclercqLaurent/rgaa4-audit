<?php

declare(strict_types=1);

namespace Rgaa\PhpStan;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Limite le nombre de paramètres d'une fonction/méthode (règle Sonar S107,
 * cf. socle QA : « max 7 paramètres »). Au-delà, regrouper les paramètres liés
 * dans un Value Object.
 *
 * @implements Rule<FunctionLike>
 */
final class TooManyParametersRule implements Rule
{
    public function __construct(private readonly int $maxParameters = 7)
    {
    }

    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $count = \count($node->getParams());
        if ($count <= $this->maxParameters) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'Cette fonction comporte %d paramètres ; le maximum autorisé est %d. '
                . 'Regrouper les paramètres liés dans un Value Object.',
                $count,
                $this->maxParameters,
            ))->identifier('rgaa.tooManyParameters')->build(),
        ];
    }
}
