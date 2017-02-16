<?php

namespace Laiz\Filter\Functor;

use function Laiz\Filter\filterMap;

class Filter implements \Laiz\Func\Functor
{
    public static function fmap(callable $f, $a)
    {
        return filterMap($f, $a);
    }
}
