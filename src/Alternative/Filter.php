<?php

namespace Laiz\Filter\Alternative;

use Laiz\Filter\Functor;
use function Laiz\Filter\filterZero;
use function Laiz\Filter\filterPlus;

class Filter implements \Laiz\Func\Alternative
{
    public static function aempty()
    {
        return filterZero();
    }

    public static function aor($a, $b)
    {
        return filterPlus($a, $b);
    }
}
