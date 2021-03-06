<?php
/******************************************************************************
 * Parsian - Helps you to write parsers in PHP.
 * 
 * Copyright (c) 2018 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The GPLv3 License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Parsian\Test\Pratt;

use Lechimp\Parsian\Tokenizer\Regexp;
use Lechimp\Parsian\Pratt\Symbol;
use Lechimp\Parsian\Pratt\SymbolTable;

class _SymbolTable extends SymbolTable {
    public function _add_symbol($regexp, $binding_power = 0) {
        return $this->add_symbol($regexp, $binding_power);
    }
}

class SymbolTableTest extends \PHPUnit\Framework\TestCase {
    public function setUp() {
        $this->symbol_table = new _SymbolTable();
        $this->symbol_table_mock = $this->getMockBuilder(SymbolTable::class)
            ->setMethods(["add_symbol"])
            ->getMock();
    }

    public function test_null_table() {
        $symbols = iterator_to_array($this->symbol_table);
        $this->assertEmpty($symbols);
    }

    public function test_add_symbol() {
        $s = $this->symbol_table->_add_symbol("foo", 10);
        $symbols = iterator_to_array($this->symbol_table);

        $expected = new Symbol(new Regexp("foo"), 10);
        $this->assertEquals($expected, $s);
        $this->assertEquals([$expected], $symbols);
    }

    public function test_no_double_symbol() {
        $this->symbol_table->_add_symbol("foo", 10);

        $thrown = false;
        try {
            $this->symbol_table->_add_symbol("foo", 10);
            $this->assertFalse("This should not happen.");
        }
        catch (\LogicException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_get_symbol() {
        $s1 = $this->symbol_table->_add_symbol("foo", 10);
        $s2 = $this->symbol_table->get_symbol("foo");

        $this->assertEquals($s1, $s2);
    }

    public function test_get_nonexisting_symbol() {
        $thrown = false;
        try {
            $this->symbol_table->get_symbol("foo");
            $this->assertFalse("This should not happen.");
        }
        catch (\OutOfBoundsException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_get_operator() {
        $o1 = $this->symbol_table->operator("*");
        $o2 = $this->symbol_table->get_operator("*");

        $this->assertEquals($o1, $o2);
    }

    public function test_symbol() {
        $sym = "sym";
        $binding_power = 23;
        $symbol = $this->createMock(Symbol::class);
        $this->symbol_table_mock
            ->expects($this->once())
            ->method("add_symbol")
            ->with($sym)
            ->willReturn($symbol);

        $ret = $this->symbol_table_mock->symbol($sym, $binding_power);
        $this->assertEquals($symbol, $ret);
    }

    public function test_operator() {
        $op = "op";
        $binding_power = 42;
        $symbol = $this->createMock(Symbol::class);
        $this->symbol_table_mock
            ->expects($this->once())
            ->method("add_symbol")
            ->with("[o][p]")
            ->willReturn($symbol);

        $ret = $this->symbol_table_mock->operator($op, $binding_power);
        $this->assertEquals($symbol, $ret);
    }

    public function test_literal() {
        $regexp = "re";
        $converter = function($v) { return $v; };

        $symbol_mock = $this->createMock(Symbol::class);
        $symbol_mock
            ->expects($this->once())
            ->method("null_denotation_is")
            ->with($converter)
            ->willReturn($symbol_mock);

        $this->symbol_table_mock
            ->expects($this->once())
            ->method("add_symbol")
            ->with($regexp, 0)
            ->willReturn($symbol_mock);

        $ret = $this->symbol_table_mock->literal($regexp, $converter);           
        $this->assertEquals($symbol_mock, $ret);
    }

    public function test_symbol_for_eof() {
        $s = $this->symbol_table->symbol_for_eof();
        $expected = new Symbol(new Regexp("$"), -1);
        $this->assertEquals($expected, $s);
    }
}
