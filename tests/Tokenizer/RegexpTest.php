<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Parsian\Test\Tokenizer;

use Lechimp\Parsian\Tokenizer\Regexp;

class RegexpTest extends \PHPUnit\Framework\TestCase {
    public function regexp($str) {
        return new Regexp($str);
    }

    public function test_raw() {
        $re = $this->regexp("ab");
        $this->assertEquals("ab", $re->raw());
    }

    public function test_throws_on_delim() {
        try {
            $this->regexp("%");
            $this->assertFalse("This should not happen.");
        }
        catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_match() {
        $re = $this->regexp("ab");
        $this->assertInternalType("array", $re->match("ab"));
        $this->assertNull($re->match("abc"));
        $this->assertNull($re->match("0abc"));
        $this->assertNull($re->match("0ab"));
        $this->assertNull($re->match("cd"));
    }

    public function test_match_beginning() {
        $re = $this->regexp("ab");
        $this->assertInternalType("array", $re->match_beginning("ab"));
        $this->assertInternalType("array", $re->match_beginning("abc"));
        $this->assertNull($re->match_beginning("0abc"));
        $this->assertNull($re->match_beginning("0ab"));
        $this->assertNull($re->match_beginning("cd"));
    }

    public function test_search() {
        $re = $this->regexp("ab");
        $this->assertInternalType("array", $re->search("ab"));
        $this->assertInternalType("array", $re->search("abc"));
        $this->assertInternalType("array", $re->search("0abc"));
        $this->assertInternalType("array", $re->search("0ab"));
        $this->assertNull($re->search("cd"));
    }

    public function test_matches() {
        $re = $this->regexp("(a)(b)");

        $matches = $re->match("ab");
        $this->assertEquals(["ab", "a", "b"], $matches);

        $matches = $re->match_beginning("ab");
        $this->assertEquals(["ab", "a", "b"], $matches);

        $matches = $re->search("ab");
        $this->assertEquals(["ab", "a", "b"], $matches);
    }

    public function test_dotall() {
        $re = $this->regexp("(a).(b)");

        $this->assertNull($re->match("a\nb"));
        $this->assertNull($re->match_beginning("a\nb"));
        $this->assertNull($re->search("a\nb"));

        $this->assertInternalType("array", $re->match("a\nb", true));
        $this->assertInternalType("array", $re->match_beginning("a\nb", true));
        $this->assertInternalType("array", $re->search("a\nb", true));
    }

    public function test_match_backslash() {
        $re = $this->regexp("[\\\\]");

        $this->assertInternalType("array", $re->match("\\"));
        $this->assertInternalType("array", $re->match_beginning("\\a"));
        $this->assertInternalType("array", $re->search("a\\b"));
    }

    public function test_match_namespacelike() {
        $re = $this->regexp("A[\\\\]B");

        $this->assertInternalType("array", $re->match("A\\B"));
        $this->assertInternalType("array", $re->match_beginning("A\\Ba"));
        $this->assertInternalType("array", $re->search("aA\\Bb"));
    }
}
