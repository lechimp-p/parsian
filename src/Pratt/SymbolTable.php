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

use Lechimp\Parsian\Tokenizer\Symbols;
use Lechimp\Parsian\Tokenizer\Symbol as TSymbol;
use Lechimp\Parsian\Tokenizer\Regexp;

/**
 * The symbol table knows all symbols a parser construct.
 */
class SymbolTable implements Symbols {
    /**
     * @var Symbol[]
     */
    protected $symbols = [];

    /**
     * Add a symbol to the symbol table.
     *
     * @param   string  $regexp
     * @param   int     $binding_power
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    public function symbol(string $regexp, int $binding_power = 0) : Symbol {
        return $this->add_symbol($regexp, $binding_power);
    }

    /**
     * Add an operator to the symbol table.
     *
     * Convenience, will split the given string and wrap each char in []
     * before passing it to symbol.
     *
     * @param   string  $op
     * @param   int     $binding_power
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    public function operator(string $op, int $binding_power = 0) : Symbol {
        $regexp = $this->operator_regexp($op);
        return $this->symbol($regexp, $binding_power);
    }

    /**
     * Add a literal to the symbol table, where the matches are
     * transformed using the $converter.
     *
     * @param   string      $regexp
     * @param   \Closure    $converter
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     * @return  Symbol
     */
    public function literal(string $regexp, \Closure $converter) : Symbol {
        return $this->symbol($regexp)
            ->null_denotation_is($converter);
    }

    /**
     * Get a symbol from the table by regexp.
     *
     * @throws  \OutOfBoundsException
     */
    public function get_symbol(string $regexp) : Symbol {
        if (!array_key_exists($regexp, $this->symbols)) {
            throw new \OutOfBoundsException("Unknown symbol: '$regexp'");
        }
        return $this->symbols[$regexp];
    }

    /**
     * Get an operator from the table.
     *
     * @throws  \OutOfBoundsException
     */
    public function get_operator(string $op) : Symbol {
        return $this->get_symbol($this->operator_regexp($op));
    }

    /**
     * Add a symbol to the table.
     *
     * @throws  \InvalidArgumentException if %$regexp% is not a regexp
     * @throws  \LogicException if there already is a symbol with that $regexp.
     */
    protected function add_symbol(string $regexp, int $binding_power = 0) : Symbol {
        if (array_key_exists($regexp, $this->symbols)) {
            throw new \LogicException("Symbol for regexp $regexp already exists.");
        }
        $s = $this->build_symbol($regexp, $binding_power);
        $this->symbols[$regexp] = $s;
        return $s;
    }

    /**
     * "abc" -> "[a][b][c]" 
     *
     * Makes handling operators like "*" easier.
     *
     * @param   string  $op
     * @return  string
     */
    protected function operator_regexp(string $op) : string {
        assert('is_string($op)');
        $regexp = array();
        foreach (str_split($op, 1) as $c) {
            $regexp[] = "[$c]";
        }
        return implode("", $regexp);
    }

    protected function build_symbol(string $regexp, int $binding_power) : Symbol {
        return new Symbol($this->build_regexp($regexp), $binding_power);
    }

    protected function build_regexp(string $regexp) : Regexp {
        return new Regexp($regexp);
    }

    // Symbols implementation

    /**
     * The symbol to be used for the end token.
     */
    public function symbol_for_eof() : TSymbol {
        return $this->build_symbol("$", -1);
    }

    // Iterator implementation 

    /**
     * @var int
     */
    public $position = 0;

    public function current() : TSymbol {
        return array_values($this->symbols)[$this->position];
    }

    public function key() : int {
        return $this->position;
    } 

    public function next() {
        return $this->position++; 
    }

    public function rewind() {
        return $this->position = 0;
    }

    public function valid() {
        $cnt = count($this->symbols);
        return $cnt > 0 && $this->position < $cnt;
    }
}

