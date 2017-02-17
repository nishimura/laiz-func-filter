<?php
declare(strict_types=1);

namespace Laiz\Filter;

use function Laiz\Func\f;
use Laiz\Func\Either;

function runFilter(...$args)
{
    return f(function($m, $a) : Result{
        return ($m->unFilter())([$a, $a]);
    }, ...$args);
}

function filterPure(...$args)
{
    return f(function($a){
        return new Filter(function(array $si) use ($a) : Result{
            list($state, $_) = $si;
            return new Result\Ok($state, $a);
        });
    }, ...$args);
}

function filterBind(...$args)
{
    return f(function($m, $f){
        return new Filter(function(array $si) use ($m, $f): Result {
            list ($state, $input) = $si;
            
            $f2 = $m->unFilter();
            $result = $f2($si);
            if ($result instanceof Result\Ok){
                return ($f($result->result())->unFilter())([$result->state(), $input]);
            }elseif ($result instanceof Result\EmptyOk){
                return new Result\EmptyOk();
            }elseif ($result instanceof Result\EmptyError){
                return new Result\EmptyError();
            }elseif ($result instanceof Result\Error){
                return new Result\Error($result->state(), $result->message());
            }else{
                throw new Exception('Type Error');
            }
        });
    }, ...$args);
}

function filterMap(...$args)
{
    return f(function($f, $m){
        return new Filter(function(array $si) use ($f, $m): Result {
            list($state, $input) = $si;
            $f2 = $m->unFilter();
            $result = $f2($si);
            if ($result instanceof Result\Ok){
                return new Result\Ok($result->state(), $f($result->result()));
            }elseif ($result instanceof Result\EmptyOk){
                return new Result\EmptyOk();
            }elseif ($result instanceof Result\EmptyError){
                return new Result\EmptyError();
            }elseif ($result instanceof Result\Error){
                return new Result\Error($result->state(), $result->message());
            }else{
                throw new Exception('Type Error');
            }
        });
    }, ...$args);
}

function filterZero()
{
    return new Filter(function(array $si) {
        return new Result\EmptyError();
    });
}

function filterPlus(...$args)
{
    return f(function($m1, $m2){
        return new Filter(function(array $si) use ($m1, $m2) {
            list($state, $input) = $si;
            $f1 = $m1->unFilter();
            $result = $f1($si);
            if ($result instanceof Result\Ok)
                return $result;

            return ($m2->unFilter())($si);
        });
    }, ...$args);
}

/*
 * Filter s in1 out1 -> Filter s out1 out2 -> Filter s in1 out2
 */
function combine(...$args)
{
    return f(function($m1, $m2){
        return new Filter(function(array $si) use ($m1, $m2){
            list($state, $input) = $si;
            $f1 = $m1->unFilter();
            $result = $f1($si);
            if ($result instanceof Result\Ok){
                $f2 = $m2->unFilter();
                return $f2([$result->state(), $result->result()]);
            }elseif ($result instanceof Result\EmptyOk){
                return new Result\EmptyOk();
            }elseif ($result instanceof Result\EmptyError){
                return new Result\EmptyError();
            }elseif ($result instanceof Result\Error){
                return new Result\Error($result->state(), $result->message());
            }else{
                throw new Exception('Type Error');
            }
        });
    }, ...$args);
}


/*
 * (String -> Either String a) -> Bool -> (s, String) -> Result s a
 */
function mkFilter(...$args)
{
    return f(function(callable $f, bool $required, array $si): Result {
        list($state, $input) = $si;

        if ($input === ''){
            if ($required)
                return new Result\EmptyError();
            else
                return new Result\EmptyOk();
        }

        // type check
        $wrap = function(string $input) use ($f): Either {
            return $f($input);
        };
        return $wrap($input)->either(function($left) use ($state) {
            return new Result\Error($state, $left);
        }, function($right) use ($state) {
            return new Result\Ok($state, $right);
        });;
    }, ...$args);
}

/*
 * Utility Functions
 */

// Filter s String Int
function toInt() : Filter
{
    return new Filter(mkFilter(function(string $input){
        if (preg_match('/^-?[0-9]+$/', $input))
            return new Either\Right(+($input));
        else
            return new Either\Left("Error toInt: [$input] is not int");
    }, true));
}

// for primary key (1...n)
function toId()
{
    return new Filter(mkFilter(function(string $input){
        if (preg_match('/^[1-9][0-9]*$/', $input))
            return new Either\Right(+($input));
        else
            return new Either\Left("Error toInt: [$input] is not Id");
    }, true));
}

function min($n)
{
    return new Filter(function(array $si) use ($n) {
        list($state, $input) = $si;

        if ($input >= $n)
            return new Result\Ok($state, $input);
        else
            return new Result\Error($state, "Error min: Input [$input] is under than [$n]");
    });
}

function max($n)
{
    return new Filter(function(array $si) use ($n) {
        list($state, $input) = $si;

        if ($input <= $n)
            return new Result\Ok($state, $input);
        else
            return new Result\Error($state, "Error max: Input [$input] is over than [$n]");
    });
}

// String -> Filter s String o -> Filter s String o
function option(...$args)
{
    return f(function(string $default, Filter $m): Filter {
        return new Filter(function(array $si) use ($default, $m){
            list($state, $input) = $si;
            if ($input === '')
                return ($m->unFilter())([$state, $default]);
            else
                return ($m->unFilter())($si);
        });
    }, ...$args);
}

// Filter s String o -> Filter s String o
function optional(...$args)
{
    return f(function(Filter $m): Filter{
        return new Filter(function(array $si) use ($m){
            list($state, $input) = $si;
            if ($input === '')
                return new Result\EmptyOk();
            else
                return ($m->unFilter())($si);
        });
    }, ...$args);
}
