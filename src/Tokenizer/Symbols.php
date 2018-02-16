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
 * A simple list of symbols.
 */
interface Symbols extends \Iterator {
    /**
     * This needs to return Symbols.
     */
    public function current() : Symbol;
}
