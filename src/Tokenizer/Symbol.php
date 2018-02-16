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
 * A symbol from the view of the tokenizer.
 */
interface Symbol {
    /**
     * The regexp to match this token.
     */
    public function regexp() : Regexp;  
}
