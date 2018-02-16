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
    protected $matches;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    public function __construct(Symbol $symbol, array $matches, int $line, int $column) {
        $this->symbol = $symbol;
        $this->matches = $matches;
        $this->line = $line;
        $this->column = $column;
    } 

    /**
     * Get the line the token was found.
     */
    public function line() : int {
        return $this->line;
    }

    /**
     * Get the column the token was found.
     */
    public function column() : int {
        return $this->column;
    }
}
