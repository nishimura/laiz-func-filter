<?php

namespace Laiz\Test\Filter;

use Laiz\Func\Loader;
use function Laiz\Func\Functor\fmap;
use function Laiz\Func\Alternative\aor;
use function Laiz\Filter\runFilter;
use function Laiz\Filter\filterPure;
use function Laiz\Filter\combine;
use function Laiz\Filter\{toInt, toId, min, max, option, optional, filterZero};
use Laiz\Filter;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    public function testPure()
    {
        $m = filterPure('a');
        $this->assertInstanceOf(Filter\Filter::class, $m);

        $ret = runFilter($m, 1);
        $this->assertEquals(1, $ret->state());
        $this->assertEquals('a', $ret->result());

    }

    public function testFmap()
    {
        $f = function($a){ return ord($a); };
        $m = filterPure('a');

        $ret = runFilter(fmap($f, $m), 1);

        $this->assertInstanceOf(Filter\Result::class, $ret);
        $this->assertEquals(97, $ret->result()); // ord('a') => 97
        $this->assertEquals(1, $ret->state());
    }

    public function testBind()
    {
        $m = filterPure('a');
        $f = function($a){ return filterPure($a . 'b'); };

        $ret = runFilter($m->bind($f), 1);
        $this->assertEquals('ab', $ret->result());
        $this->assertEquals(1, $ret->state());
    }

    public function testToInt()
    {
        $m = toInt();
        $ret = runFilter($m, '5');
        $this->assertSame(5, $ret->result());
        $this->assertSame('5', $ret->state());

        $ret = runFilter($m, '0.5');
        $this->assertInstanceOf(Filter\Result\Error::class, $ret);
        $this->assertContains('Error toInt', $ret->message());
        $this->assertSame('0.5', $ret->state());
    }

    /**
     * @expectedException TypeError
     */
    public function testToIntException(){
        $m = toInt();
        $ret = runFilter($m, 3);
        $this->fail('3 is not string');
    }

    public function testMin()
    {
        $m = min(3);
        $ret = runFilter($m, 5);
        $this->assertInstanceOf(Filter\Result\Ok::class, $ret);
        $this->assertEquals(5, $ret->result());
        $this->assertEquals(5, $ret->state());

        $ret = runFilter($m, 2);
        $this->assertInstanceOf(Filter\Result\Error::class, $ret);
        $this->assertContains('Error min', $ret->message());
        $this->assertSame(2, $ret->state());
    }

    public function testCombine()
    {
        $converter = toInt();
        $validator = max(255);
        $m = combine($converter, $validator);

        $ret = runFilter($m, '15');
        $this->assertInstanceOf(Filter\Result\Ok::class, $ret);
        $this->assertSame(15, $ret->result());
        $this->assertSame('15', $ret->state());
    }

    public function testCombineError()
    {
        $converter = toInt();
        $validator = max(255);
        $m = combine($converter, $validator);

        $ret = runFilter($m, '');
        $this->assertInstanceOf(Filter\Result\EmptyError::class, $ret);

        $ret = runFilter($m, '255');
        $this->assertInstanceOf(Filter\Result\Ok::class, $ret);

        $ret = runFilter($m, '256');
        $this->assertInstanceOf(Filter\Result\Error::class, $ret);
        $this->assertContains('Error max', $ret->message());
        $this->assertSame('256', $ret->state());
    }

    /**
     * @expectedException TypeError
     */
    public function testCombineTypeError()
    {
        $converter = toInt();
        $validator = max(255);
        $m = combine($converter, $validator);
        $ret = runFilter($m, 3);
        $this->fail('3 is not string');
    }

    public function testOption()
    {
        $m = option('999', toInt());
        $ret = runFilter($m, '5');
        $this->assertSame(5, $ret->result());
        $this->assertSame('5', $ret->state());

        $ret = runFilter($m, '');
        $this->assertSame(999, $ret->result());
        $this->assertSame('', $ret->state());
    }

    public function testOptional()
    {
        $m = optional(toInt());
        $ret = runFilter($m, '5');
        $this->assertSame(5, $ret->result());
        $this->assertSame('5', $ret->state());

        $ret = runFilter($m, '');
        $this->assertInstanceOf(Filter\Result\EmptyOk::class, $ret);
    }

    public function testEmpty()
    {
        $m = filterZero();
        $ret = runFilter($m, 'a');
        $this->assertInstanceOf(Filter\Result\EmptyError::class, $ret);
    }

    public function testOr()
    {
        $min = min(20);
        $max = max(10);
        $m = aor($min, $max);

        $ret = runFilter($m, 25);
        $this->assertInstanceOf(Filter\Result\Ok::class, $ret);

        $ret = runFilter($m, 5);
        $this->assertInstanceOf(Filter\Result\Ok::class, $ret);

        $ret = runFilter($m, 15);
        $this->assertInstanceOf(Filter\Result\Error::class, $ret);
    }
}
