<?php
/******************************************************************************
 * Parsian - Helps you to write parsers in PHP.
 * 
 * Copyright (c) 2018 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The GPLv3 License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Parsian\Pratt;

use Lechimp\Parsian\Tokenizer\Regexp;
use Lechimp\Parsian\Tokenizer\Symbol as TSymbol;

/**
 * A symbol known to the parser.
 */
class Symbol implements TSymbol {
    /**
     * @var Regexp
     */
    protected $regexp;

    /**
     * @var int
     */
    protected $binding_power;

    /**
     * This defines what a token means when appearing in the initial position
     * of an expression.
     *
     * @var \Closure|null
     */
    protected $null_denotation = null;

    /**
     * This defines what a token means when appearing inside an expression
     * to the left of the rest.
     *
     * @var \Closure|null
     */
    protected $left_denotation = null;

    public function __construct(Regexp $regexp, int $binding_power) {
        $this->regexp = $regexp;
        $this->binding_power = $binding_power;
    }

    /**
     * @return  Regexp
     */
    public function regexp() : Regexp {
        return $this->regexp;
    }

    /**
     * @return  int
     */
    public function binding_power() : int {
        return $this->binding_power;
    }

    /**
     * @param   \Closure    $led
     * @return  self
     */
    public function null_denotation_is(\Closure $led) : Symbol {
        assert('$this->null_denotation === null');
        $this->null_denotation = $led;
        return $this;
    }

    /**
     * @param   array   $matches
     * @return  mixed
     */
    public function null_denotation(array $matches) {
        $denotation = $this->denotation("null", $matches); 
        return $denotation($matches);
    }

    /**
     * @param   \Closure    $led
     * @return  self
     */
    public function left_denotation_is(\Closure $led) : Symbol {
        assert('$this->left_denotation === null');
        $this->left_denotation = $led;
        return $this;
    }

    /**
     * @param   mixed   $left
     * @param   array   $matches
     * @return  mixed
     */
    public function left_denotation($left, array $matches) {
        $denotation = $this->denotation("left", $matches); 
        return $denotation($left, $matches);
    }

    // HELPER

    protected function denotation($which, array $matches) {
        $which = $which."_denotation";
        $denotation = $this->$which;
        if ($this->$which === null) {
            $m = $matches[0];
            throw new \LogicException("Syntax Error: $m");
        }
        return $denotation;
    }
}
