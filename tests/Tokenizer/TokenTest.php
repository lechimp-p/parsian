<?php
/******************************************************************************
 * Parsian - Helps you to write parsers in PHP.
 *
 * Copyright (c) 2018 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under the GPLv3 License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Parsian\Test\Tokenizer;

use Lechimp\Parsian\Tokenizer as T;

class TokenTest extends \PHPUnit\Framework\TestCase {
    public function setUp() {
        $this->symbol = $this->createMock(T\Symbol::class);
        $this->match = ["my", "match"];
        $this->line = 23;
        $this->column = 42;
        $this->token = new T\Token($this->symbol, $this->match, $this->line, $this->column);
    }

    public function test_symbol() {
        $this->assertEquals($this->symbol, $this->token->symbol());
    }

    public function test_match() {
        $this->assertEquals($this->match, $this->token->match());
    }

    public function test_line() {
        $this->assertEquals($this->line, $this->token->line());
    }

    public function test_column() {
        $this->assertEquals($this->column, $this->token->column());
    }
}
