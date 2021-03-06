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

/**
 * A list of tokens.
 */
class Tokens implements \Iterator {
    /**
     * @var Symbols
     */
    protected $symbols;

    /**
     * @var int 
     */
    protected $position;

    /**
     * @var Token[]
     */
    protected $tokens;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $unparsed;

    /**
     * @var int
     */
    protected $parsing_position;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    public function __construct(Symbols $symbols, string $source) {
        $this->symbols = $symbols;
        $this->position = 0;
        $this->tokens = [];
        $this->source = $source;
        $this->unparsed = $source;
        $this->parsing_position = 0;
        $this->line = 1;
        $this->column = 1;
    }

    /**
     * @inheritdocs
     */
    public function current() : Token {
        $this->maybe_parse_next_token();
        if (count($this->tokens) <= $this->position) {
            throw new EOFReachedException("No more tokens here.");
        }
        return $this->tokens[$this->position];
    }

    /**
     * @inheritdocs
     */
    public function key() : int {
        return $this->position;
    }

    /**
     * @inheritdocs
     */
    public function next() {
        $this->position++;
        $this->maybe_parse_next_token();
    }

    /**
     * @inheritdocs
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * @inheritdocs
     */
    public function valid() : bool {
        $this->maybe_parse_next_token();
        return count($this->tokens) > $this->position;
    }

    /**
     * Try to parse the next token if there are currently not enough tokens
     * in the tokens-array to get a token for the current position.
     *
     * @throws  NoMatchException if next token can not be parsed.
     */
    protected function maybe_parse_next_token() {
        if (count($this->tokens) <= $this->position) {
            $this->parse_next_token();
        }
    }

    /**
     * Try to parse the next token from the source.
     *
     * @throws  NoMatchException if next token can not be parsed.
     */
    protected function parse_next_token() {
        if ($this->is_everything_parsed()) {
            return;
        }

        foreach ($this->symbols as $symbol) {
            $re = $symbol->regexp();
            if ($matches = $re->match_beginning($this->unparsed, true)) {
                $this->tokens[] = new Token
                    ( $symbol
                    , array_values($matches)
                    , $this->line
                    , $this->column
                    );
                $this->advance($matches[0]);
                return;
            }
        }

        throw new NoMatchException($this->unparsed, $this->line, $this->column);
    }


    /**
     * Go forward in the string we have parsed so far.
     *
     * @param  string   $match
     * @return null
     */
    protected function advance(string $match) {
        $unparsed = substr($this->unparsed, strlen($match));
        $this->unparsed = ltrim
            ( $unparsed            
            , "\t \0\x0B" // don't trim linebreaks
            );
        $lines = explode("\n", $match);
        if (count($lines) > 1) {
            $this->line += count($lines) - 1;
            // account for "unusual" counting, which starts at 1 (not 0)
            $this->column = 1;
        }
        $this->column += strlen(array_pop($lines));
        // account for trimmed whitespace
        $this->column += strlen($unparsed) - strlen($this->unparsed);
    }

    /**
     * Check if everything is parsed.
     *
     * @return  bool
     */
    protected function is_everything_parsed() {
        return empty($this->unparsed);
    }
}
