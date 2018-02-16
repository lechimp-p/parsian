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

class Regexp {
    /**
     * @var string
     */
    protected $regexp;

    /**
     * @var string
     */
    protected $delim = "%";

    public function __construct(string $regexp) {
        assert('is_string($regexp)');
        if (@preg_match($this->delim.$regexp.$this->delim, "") === false) {
            throw new \InvalidArgumentException("Invalid regexp '$regexp'");
        }
        $this->regexp = $regexp;
    }

    /**
     * @return  string
     */
    public function raw() : string {
        return $this->regexp;
    }

    /**
     * Match a string with the regexp.
     *
     * @return null|array
     */
    public function match(string $str, bool $dotall = false) {
        $matches = [];
        $regexp = $this->delim."^(".$this->regexp.")$".$this->delim;
        if ($dotall) {
            $regexp .= "s";
        }
        if(preg_match($regexp, $str, $matches) === 1) {
            unset($matches[1]);
            return array_values($matches);
        } 
        return null;
    }

    /**
     * Match the beginning of a string with the regexp.
     *
     * @return null|array
     */
    public function match_beginning(string $str, bool $dotall = false) {
        $matches = [];
        $regexp = $this->delim."^(".$this->regexp.")".$this->delim;
        if ($dotall) {
            $regexp .= "s";
        }
        if(preg_match($regexp, $str, $matches) === 1) {
            unset($matches[1]);
            return array_values($matches);
        }
        return null;
    }

    /**
     * Search a string with the regexp.
     *
     * @return null|array
     */
    public function search(string $str, bool $dotall = false) {
        $matches = [];
        $regexp = $this->delim."".$this->regexp.''.$this->delim;
        if ($dotall) {
            $regexp .= "s";
        }
        if(preg_match($regexp, $str, $matches) === 1) {
            return $matches;
        }
        return null;
    }
}
