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

class ArrayWrapper implements T\Symbols {
    public $test;
    public $pos = 0;
    public function current() : T\Symbol {
        return $this->test->symbols[$this->pos];
    }
    public function key() : int {
        return $this->pos;
    }
    public function next() {
        $this->pos++;
    }
    public function rewind() {
        $this->pos = 0;
    }
    public function valid() : bool {
        return $this->pos < count($this->test->symbols);
    }
}

class TokenizerTest extends \PHPUnit\Framework\TestCase {
    public function setUp() {
        $this->symbols = [];
        $symbols = new ArrayWrapper();
        $symbols->test = $this;
        $this->tokenizer = new T\Tokenizer($symbols);
        $this->symbol_count = 0;
    }

    public function create_symbol($regexp) {
        $this->symbol_count++;
        $mock = $this->getMockBuilder(T\Symbol::class)
            ->setMethods(["regexp"])
            ->setMockClassName("Tokenizer_Symbol_{$this->symbol_count}")
            ->getMock();
        $mock
            ->method("regexp")
            ->willReturn(new T\Regexp($regexp));
        return $mock;
    }

    public function test_syntax_error() {
        $tokens = $this->tokenizer->tokens("some source.");

        $thrown = null; 
        try {
            $tokens->current();
            $this->assertTrue("This should not happen.");
        }
        catch (T\Exception $e) {
            $thrown = $e;
        }
        $this->assertInstanceOf(T\Exception::class, $thrown);
        $this->assertEquals($e->unmatched_text(), "some source.");
    }

    public function test_eof() {
        $tokens = $this->tokenizer->tokens("");
        $thrown = false;
        try {
            $token = $tokens->current();
        }
        catch (\RuntimeException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_one_token() {
        $symbol = $this->create_symbol("\w+");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("hello");
        $token1 = $tokens->current();
        $tokens->next();

        $expected1 = new T\Token($symbol, ["hello"], 1, 1);
        $this->assertEquals($expected1, $token1);
    }

    public function test_two_tokens() {
        $symbol = $this->create_symbol("\w+");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("hello world");
        $token1 = $tokens->current();
        $tokens->next();
        $token2 = $tokens->current();

        $expected1 = new T\Token($symbol, ["hello"], 1, 1);
        $this->assertEquals($expected1, $token1);
        $expected2 = new T\Token($symbol, ["world"], 1, 7);
        $this->assertEquals($expected2, $token2);
    }

    public function test_syntax_error2() {
        $symbol = $this->create_symbol("\d+");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("hello world");

        $thrown = null; 
        try {
            $tokens->current();
            $this->assertTrue("This should not happen.");
        }
        catch (T\Exception $e) {
            $thrown = $e;
        }
        $this->assertInstanceOf(T\Exception::class, $thrown);
        $this->assertEquals($e->unmatched_text(), "hello world");
    }

    public function test_rewind() {
        $symbol = $this->create_symbol("\w+");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("hello world");
        $tokens->next();
        $tokens->rewind();
        $token = $tokens->current();

        $expected = new T\Token($symbol, ["hello"], 1, 1);
        $this->assertEquals($expected, $token);
    }

    public function test_key() {
        $symbol = $this->create_symbol("\w+");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("hello world");
        $key1 = $tokens->key();
        $tokens->next();
        $key2 = $tokens->key();
        $tokens->next();
        $key3 = $tokens->key();

        $this->assertEquals(0, $key1);
        $this->assertEquals(1, $key2);
        $this->assertEquals(2, $key3);
    }

    public function test_valid() {
        $symbol = $this->create_symbol("\w+");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("hello world");
        $this->assertTrue($tokens->valid());
        $tokens->next();
        $this->assertTrue($tokens->valid());
        $tokens->next();
        $this->assertFalse($tokens->valid());
    }

    public function test_proper_regexp_grouping() {
        $symbol = $this->create_symbol("(a)|(b)");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("cb");
        try {
            $tokens->current();
            $this->assertTrue("This should not happen.");
        }
        catch (T\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function test_source_position_1() {
        $symbol = $this->create_symbol("(a)|(b)");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("ab");
        $token = $tokens->current();

        $this->assertEquals(1, $token->line());
        $this->assertEquals(1, $token->column());
    }

    public function test_source_position_2() {
        $symbol = $this->create_symbol("(a)|(b)");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("ab");
        $tokens->next();
        $token = $tokens->current();

        $this->assertEquals(1, $token->line());
        $this->assertEquals(2, $token->column());
    }

    public function test_source_position_3() {
        $symbol = $this->create_symbol("(a)|(b)|\n");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("a\nb");
        $tokens->next();
        $tokens->next();
        $token = $tokens->current();

        $this->assertEquals(2, $token->line());
        $this->assertEquals(1, $token->column());
    }

    public function test_source_position_after_rewind() {
        $symbol = $this->create_symbol("(a)|(b)|\n");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("a\nb");
        $tokens->next();
        $tokens->next();
        $tokens->rewind();
        $token = $tokens->current();

        $this->assertEquals(1, $token->line());
        $this->assertEquals(1, $token->column());
    }

    public function test_skip_whitespace() {
        $symbol = $this->create_symbol("(a)|(b)");
        $this->symbols[] = $symbol;

        $tokens = $this->tokenizer->tokens("a   b");
        $tokens->next();
        $token = $tokens->current();

        $this->assertEquals(1, $token->line());
        $this->assertEquals(5, $token->column());
    }
}
