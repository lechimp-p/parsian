<?php
/******************************************************************************
 * Parsian - Helps you to write parsers in PHP.
 * 
 * Copyright (c) 2018 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The GPLv3 License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Parsian\Tokenizer;

class Token {
    /**
     * @var Symbol
     */
    protected $symbol;

    /**
     * @var string[]
     */
    protected $match;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    public function __construct(Symbol $symbol, array $match, int $line, int $column) {
        $this->symbol = $symbol;
        $this->match = $match;
        $this->line = $line;
        $this->column = $column;
    } 

    /**
     * Get the symbol of the token.
     */
    public function symbol() : Symbol {
        return $this->symbol;
    }

    /**
     * Get the match of the token.
     *
     * @return  string[] 
     */
    public function match() : array {
        return $this->match;
    }

    /**
     * Get the line the token was found in.
     */
    public function line() : int {
        return $this->line;
    }

    /**
     * Get the column the token was found in.
     */
    public function column() : int {
        return $this->column;
    }
}
