<?php

declare(strict_types=1);

namespace TheCodingMachine\PHPStan\Rules\Exceptions;

use Exception;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\ShouldNotHappenException;
use RuntimeException;
use TheCodingMachine\PHPStan\Utils\PrefixGenerator;
use Throwable;

use function in_array;

/**
 * When catching \Exception, \RuntimeException or \Throwable, the exception MUST be thrown again
 * (unless you are developing an exception handler...)
 *
 * @implements Rule<Catch_>
 */
class MustRethrowRule implements Rule
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
        // Let's only apply the filter to \Exception, \RuntimeException or \Throwable
        $exceptionType = null;
        foreach ($node->types as $type) {
            if (in_array((string)$type, [Exception::class, RuntimeException::class, Throwable::class], true)) {
                $exceptionType = (string)$type;
                break;
            }
        }

        if ($exceptionType === null) {
            return [];
        }

        // Let's visit and find a throw.
        $visitor = new class() extends NodeVisitorAbstract {
            /**
             * @var bool
             */
            private bool $throwFound = false;

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Node\Stmt\Expression && $node->expr instanceof Node\Expr\Throw_) {
                    $this->throwFound = true;
                }

                foreach ($node->getComments() as $comment) {
                    if (strpos($comment->getText(), '@ignoreException') !== false) {
                        $this->throwFound = true;
                    }
                }
            }

            /**
             * @return bool
             */
            public function isThrowFound(): bool
            {
                return $this->throwFound;
            }
        };

        $traverser = new NodeTraverser();

        $traverser->addVisitor($visitor);

        $traverser->traverse($node->stmts);

        $errors = [];

        if (!$visitor->isThrowFound()) {
            $message = sprintf('%scaught "%s" must be rethrown. Either catch a more specific exception, add a "throw" clause in the "catch" block to propagate the exception or add a "// @ignoreException" comment.', PrefixGenerator::generatePrefix($scope), $exceptionType);
            $errors[] = RuleErrorBuilder::message($message)
                ->line($node->getStartLine())
                ->file($scope->getFile())
                ->build();
        }

        return $errors;
    }
}
