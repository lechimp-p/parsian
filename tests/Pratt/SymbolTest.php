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

use Lechimp\Parsian\Pratt\Symbol;
use Lechimp\Parsian\Tokenizer\Regexp;

class SymbolText extends \PHPUnit\Framework\TestCase {
    protected function symbol($regexp, $binding_power) {
        return new Symbol(new Regexp($regexp), $binding_power);
    }  

    public function test_no_null_denotation() {
        $s = $this->symbol("a", 10);
        $thrown = false;
        try {
            $arr = array("match");
            $s->null_denotation($arr);
            $this->assertFalse("This should not happen.");
        }
        catch (\LogicException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_null_denotation_mutable() {
        $s = $this->symbol("a", 10);
        $s2 = $s->null_denotation_is(function(array $match) {
            return $match[0];
        });

        $this->assertEquals($s, $s2);
    }

    public function test_null_denotation() {
        $s = $this->symbol("a", 10);
        $s2 = $s->null_denotation_is(function(array $match) {
            return $match[0];
        });

        $arr = array("match");
        $res = $s->null_denotation($arr);
        $this->assertEquals("match", $res);
    }

    public function test_no_left_denotation() {
        $s = $this->symbol("a", 10);
        $thrown = false;
        try {
            $arr = array("match");
            $s->left_denotation("foo", $arr);
            $this->assertFalse("This should not happen.");
        }
        catch (\LogicException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown);
    }

    public function test_left_denotation_mutable() {
        $s = $this->symbol("a", 10);
        $s2 = $s->left_denotation_is(function($left, array $match) {
            return $left + $match[0];
        });

        $this->assertEquals($s, $s2);
    }

    public function test_left_denotation() {
        $s = $this->symbol("a", 10);
        $s2 = $s->left_denotation_is(function($left, array $match) {
            return $left + $match[0];
        });

        $arr = array(2);
        $res = $s->left_denotation(1, $arr);
        $this->assertEquals(3, $res);
    }
}
