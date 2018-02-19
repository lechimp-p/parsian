<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Parsian\Test\Pratt;

use Lechimp\Parsian\Pratt\Parser as ParserBase;
use Lechimp\Parsian\Tokenizer as T;
use Lechimp\Parsian\Pratt\SymbolTable;

class Parser extends ParserBase {
    protected function add_symbols_to(SymbolTable $table) {
        $table->literal("\\d+", function(array &$matches) {
                return intval($matches[0]);
            });
        $table->operator("+", 10)
            ->left_denotation_is(function($left, array &$matches) {
                return $left + $this->expression(10);
            });
        $table->operator("-", 10)
            ->left_denotation_is(function($left, array &$matches) {
                return $left - $this->expression(10);
            });
        $table->operator("**", 30)
            ->left_denotation_is(function($left, array &$matches) {
                return pow($left, $this->expression(30-1));
            });
        $table->operator("*", 20)
            ->left_denotation_is(function($left, array &$matches) {
                return $left * $this->expression(20);
            });
        $table->operator("/", 20)
            ->left_denotation_is(function($left, array &$matches) {
                return $left / $this->expression(20);
            });
        $table->operator("(")
            ->null_denotation_is(function(array &$matches) use ($table) {
                $res = $this->expression(0);
                $this->advance($table->get_operator(")"));
                return $res;
            });
        $table->operator(")");
    }
}

class ParsingTest extends \PHPUnit\Framework\TestCase {
    public function setUp() {
        $this->parser = new Parser();
    }

    public function parse($expr) {
        return $this->parser->parse($expr);
    }

    public function test_1() {
        $res = $this->parse("1");
        $this->assertEquals(1, $res);
    }

    public function test_add() {
        $res = $this->parse("1 + 2");
        $this->assertEquals(3, $res);
    }

    public function test_subtract() {
        $res = $this->parse("1 - 2");
        $this->assertEquals(-1, $res);
    }

    public function test_multiply() {
        $res = $this->parse("2 * 3");
        $this->assertEquals(6, $res);
    }

    public function test_binding() {
        $res = $this->parse("2 * 3 - 1");
        $this->assertEquals(5, $res);
    }

    public function test_pow() {
        $res = $this->parse("2 ** 3");
        $this->assertEquals(8, $res);
    }

    public function test_right_binding() {
        $res = $this->parse("2 ** 3 ** 2");
        $this->assertEquals(512, $res);
    }

    public function test_parantheses() {
        $res = $this->parse("2 * ( 3 - 1 )");
        $this->assertEquals(4, $res);
    }

    public function test_parantheses_2() {
        $res = $this->parse("( 3 - 1 )");
        $this->assertEquals(2, $res);
    }

    public function test_no_space() {
        $res = $this->parse("(3-1)");
        $this->assertEquals(2, $res);
    }

    public function test_empty() {
        $thrown = false;
        try {
            $res = $this->parse("");
            $this->assertFalse("This should not happen.");
        }
        catch (\Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_incomplete() {
        $thrown = false;
        try {
            $res = $this->parse("2 + 4 5");
            $this->assertFalse("This should not happen.");
        }
        catch (\Exception $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_error() {
        $thrown = false;
        try {
            $this->parse("4-a");
            $this->assertFalse("This should not happen.");
        }
        catch (T\Exception $e) {
            $this->assertEquals(1, $e->line());
            $this->assertEquals(3, $e->column());
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }
}
