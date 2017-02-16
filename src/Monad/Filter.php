<?php

namespace Laiz\Filter\Monad;

use Laiz\Filter\Applicative;
use function Laiz\Filter\filterBind;

class Filter extends Applicative\Filter implements \Laiz\Func\Monad
{
    public static function ret($a)
    {
        return parent::pure($a);
    }

    public static function bind($m, callable $f)
    {
        return filterBind($m, $f);
    }
}
