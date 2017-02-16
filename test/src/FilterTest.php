<?php

namespace Laiz\Test\Filter;

use Laiz\Func\Loader;
use function Laiz\Func\Functor\fmap;
use function Laiz\Filter\validate;
use function Laiz\Filter\filterPure;
use Laiz\Filter;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    public function testPure()
    {
        $m = filterPure('a');
        $this->assertInstanceOf(Filter\Filter::class, $m);

        $ret = validate($m, 1);
        $this->assertEquals(1, $ret->state());
        $this->assertEquals('a', $ret->result());

    }

    public function testFmap()
    {
        $f = function($a){ return ord($a); };
        $m = filterPure('a');

        $ret = validate(fmap($f, $m), 1);

        $this->assertInstanceOf(Filter\Result::class, $ret);
        $this->assertEquals(97, $ret->result()); // ord('a') => 97
        $this->assertEquals(1, $ret->state());
    }
}
