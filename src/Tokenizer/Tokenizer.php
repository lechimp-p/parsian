<?php
/******************************************************************************
 * Parsian - Helps you to write parsers in PHP.
 * 
 * Copyright (c) 2018 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under the GPLv3 License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Parsian\Tokenizer;

/**
 * Attempts to break a string into tokens according to a list of symbols.
 */
class Tokenizer {
    /**
     * @var Symbols
     */
    protected $symbols;

    public function __construct(Symbols $symbols) {
        $this->symbols = $symbols;
    }

    /**
     * Get the tokens from the given source.
     */
    public function tokens(string $source) : Tokens {
        return new Tokens($this->symbols, $source);
    }
}
