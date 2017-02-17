<?php
declare(strict_types=1);

namespace Laiz\Filter;

use Laiz\Func\CallTrait;
use Laiz\Func\Any;
use function Laiz\Func\Applicative\{const1, const2};

/*
 * newtype Filter state input output
 *   = Filter { unFilter :: (state, input) -> Result state output
 */
class Filter
{
    use CallTrait;

    private $f;
    public function __construct(callable $f)
    {
        $this->f = $f;
    }

    public function unFilter()
    {
        return $this->f;
    }

    /**
     * Override Applicative
     */
    public function const1($b)
    {
        if ($b instanceof Any)
            $b = $b->cast($this);
        return const1($this, $b);
    }

    /**
     * Override Applicative
     */
    public function const2($b)
    {
        if ($b instanceof Any)
            $b = $b->cast($this);
        return const2($this, $b);
    }

    public function aor($b)
    {
        if ($b instanceof Any)
            $b = $b->cast($this);
        return Alternative\Filter::aor($this, $b);
    }
    public function ap($b)
    {
        if ($b instanceof Any)
            $b = $b->cast($this);
        return Applicative\Filter::ap($this, $b);
    }
    public function bind($f)
    {
        return Monad\Filter::bind($this, $f);
    }
}
