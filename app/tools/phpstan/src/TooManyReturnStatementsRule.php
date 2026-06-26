<?php

declare(strict_types=1);

namespace Rgaa\PhpStan;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Limite le nombre d'instructions `return` par fonction/méthode (règle Sonar S1142,
 * cf. socle QA : « max 3 return »). Les fonctions imbriquées (closures, arrow
 * functions) sont comptées séparément, pas additionnées à la fonction parente.
 *
 * @implements Rule<FunctionLike>
 */
final class TooManyReturnStatementsRule implements Rule
{
    public function __construct(private readonly int $maxReturns = 3)
    {
    }

    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $stmts = $node->getStmts();
        if ($stmts === null) {
            return [];
        }

        $count = $this->countReturns($stmts);
        if ($count <= $this->maxReturns) {
            return [];
        }

        return [
            RuleErrorBuilder::message(\sprintf(
                'Cette fonction comporte %d instructions return ; le maximum autorisé est %d.',
                $count,
                $this->maxReturns,
            ))->identifier('rgaa.tooManyReturns')->build(),
        ];
    }

    /**
     * @param Node[] $stmts
     */
    private function countReturns(array $stmts): int
    {
        $visitor = new class () extends NodeVisitorAbstract {
            public int $count = 0;

            public function enterNode(Node $node): ?int
            {
                // Ne pas descendre dans les fonctions imbriquées : leurs return leur appartiennent.
                if ($node instanceof FunctionLike) {
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
                if ($node instanceof Return_) {
                    ++$this->count;
                }

                return null;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

        return $visitor->count;
    }
}
