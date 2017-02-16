<?php

namespace Laiz\Filter;

use function Laiz\Func\f;

function validate(...$args)
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
                return ($f($result->result())->unFilter())($result->state(), $input);
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
