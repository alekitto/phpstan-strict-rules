<?php

namespace TestThrowException;

class MyCatchException extends \Exception
{
}

function foo()
{
    throw new MyCatchException('');
}

function bar()
{
    throw new \Exception('');
}

function baz()
{
    try {
        // We need to do something or phpstan will skip the evaluation of the catch block
        bar();
    } catch (\Exception $e) {
        // This is ok
        throw $e;
    }
}
