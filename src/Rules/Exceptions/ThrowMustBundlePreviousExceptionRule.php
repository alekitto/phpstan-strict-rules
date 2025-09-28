<?php

declare(strict_types=1);

namespace TheCodingMachine\PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * When throwing into a catch block, checks that the previous exception is passed to the new "throw" clause
 * (the initial stack trace must not be lost).
 *
 * @implements Rule<Catch_>
 */
class ThrowMustBundlePreviousExceptionRule implements Rule
{
    public function getNodeType(): string
    {
        return Catch_::class;
    }

    /**
     * @param Catch_ $node
     * @param Scope  $scope
     *
     * @return RuleError[]
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->var === null) {
            return [];
        }

        $visitor = new class($node->var->name) extends NodeVisitorAbstract {
            private string $caughtVariableName;
            private int $exceptionUsedCount = 0;

            /** @var Node\Expr\Throw_[] */
            private array $unusedThrows = [];

            public function __construct(string $caughtVariableName)
            {
                $this->caughtVariableName = $caughtVariableName;
            }

            /** @inheritDoc */
            public function leaveNode(Node $node)
            {
                if ($node instanceof Node\Expr\Variable && $node->name === $this->caughtVariableName) {
                    $this->exceptionUsedCount++;
                }

                // If the variable is used in the context of a method call (like $e->getMessage()), the exception is not passed as a "previous exception".
                if ($node instanceof Node\Expr\MethodCall && $node->var instanceof Node\Expr\Variable && $node->var->name === $this->caughtVariableName) {
                    $this->exceptionUsedCount--;
                }

                if ($node instanceof Node\Expr\Throw_ && $this->exceptionUsedCount === 0) {
                    $this->unusedThrows[] = $node;
                }

                return null;
            }

            /** @return Node\Expr\Throw_[] */
            public function getUnusedThrows(): array
            {
                return $this->unusedThrows;
            }
        };

        $traverser = new NodeTraverser();

        $traverser->addVisitor($visitor);
        $traverser->traverse($node->stmts);

        $errors = [];

        foreach ($visitor->getUnusedThrows() as $throw) {
            $message = sprintf('Thrown exceptions in a catch block must bundle the previous exception (see throw statement line %d).', $throw->getLine());
            $errors[] = RuleErrorBuilder::message($message)
                ->line($node->getStartLine())
                ->file($scope->getFile())
                ->build();
        }

        return $errors;
    }
}
