<?php

namespace Laiz\Filter\Result;

class Error implements \Laiz\Filter\Result
{
    private $state;
    private $message;
    public function __construct($state, string $message)
    {
        $this->state = $state;
        $this->message = $message;
    }

    public function state(){ return $this->state; }
    public function message(){ return $this->message; }
}
