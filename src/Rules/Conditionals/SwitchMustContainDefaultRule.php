<?php


namespace TheCodingMachine\PHPStan\Rules\Conditionals;

use PhpParser\Node;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use TheCodingMachine\PHPStan\Utils\PrefixGenerator;

/**
 * A switch statement must always contain a "default" statement.
 *
 * @implements Rule<Switch_>
 */
class SwitchMustContainDefaultRule implements Rule
{
    public function getNodeType(): string
    {
        return Switch_::class;
    }

    /**
     * @param Switch_ $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $defaultFound = false;
        foreach ($node->cases as $case) {
            if ($case->cond === null) {
                $defaultFound = true;
                break;
            }
        }

        if (!$defaultFound) {
            $errors[] = RuleErrorBuilder::message(sprintf(PrefixGenerator::generatePrefix($scope).'switch statement does not have a "default" case.'))
                ->file($scope->getFile())
                ->line($node->getStartLine())
                ->tip('If your code is supposed to enter at least one "case" or another, consider adding a "default" case that throws an exception. More info: http://bit.ly/switchdefault')
                ->build();
        }

        return $errors;
    }
}
