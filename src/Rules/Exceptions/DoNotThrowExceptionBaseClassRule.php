<?php

declare(strict_types=1);

namespace TheCodingMachine\PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * This rule checks that the base \Exception class is never thrown. Instead, developers should subclass the \Exception
 * base class and throw the sub-type.
 *
 * @implements Rule<Throw_>
 */
class DoNotThrowExceptionBaseClassRule implements Rule
{
    public function getNodeType(): string
    {
        return Throw_::class;
    }

    /**
     * @param Throw_ $node
     * @param Scope  $scope
     *
     * @return RuleError[]
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->expr instanceof Node\Expr\New_) {
            // Only catch "throw new ..."
            return [];
        }

        $type = $scope->getType($node->expr);

        if ($type->getObjectClassNames() === ['Exception']) {
            return [
                RuleErrorBuilder::message('Do not throw the \Exception base class.')
                    ->file($scope->getFile())
                    ->line($node->getStartLine())
                    ->tip('Instead, extend the \Exception base class.')
                    ->build(),
            ];
        }

        return [];
    }
}
