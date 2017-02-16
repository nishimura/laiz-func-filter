<?php

namespace Laiz\Filter\Result;

class Ok implements \Laiz\Filter\Result
{
    private $state;
    private $result;
    public function __construct($state, $result)
    {
        $this->state = $state;
        $this->result = $result;
    }

    public function state(){ return $this->state; }
    public function result(){ return $this->result; }
}
