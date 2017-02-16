<?php

namespace Laiz\Filter\Applicative;

use Laiz\Filter\Functor;
use function Laiz\Filter\Monad\bind;
use function Laiz\Filter\filterPure;

class Filter extends Functor\Filter implements \Laiz\Func\Applicative
{
    public static function pure($a)
    {
        return filterPure($a);
    }

    public static function ap($mf, $ma)
    {
        return bind($mf, function($f) use ($ma){
            return bind($ma, function($a) use ($f){
                return self::pure($f($a));
            });
        });
    }
}
