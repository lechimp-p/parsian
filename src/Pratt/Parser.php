<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 * 
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Parsian\Pratt;

use Lechimp\Parsian\Tokenizer as T;

/**
 * Baseclass for Parsers.
 */
abstract class Parser {
    /**
     * @var SymbolTable
     */
    private $symbol_table;

    /**
     * @var T\Tokenizer
     */
    protected $tokenizer;

    /**
     * @var T\Tokens|null
     */
    protected $tokens = null;

    public function __construct() {
        $this->symbol_table = $this->build_symbol_table();
        $this->add_symbols_to($this->symbol_table);
        $this->tokenizer = $this->build_tokenizer($this->symbol_table);
        $this->tokens = null;
    }

    /**
     * Parse the string according to this parser.
     *
     * @return mixed
     */
    public function parse(string $source) {
        assert(is_null($this->tokens));
        try {
            $this->tokens = $this->tokenizer->tokens($source);
            return $this->root();
        }
        finally {
            $this->tokens = null;
        }
    }

    /**
     * The root for the parse tree. Defaults to expression.
     *
     * @return  mixed
     */
    protected function root() {
        if ($this->current_symbol() == $this->symbol_table->symbol_for_eof()) {
            throw new \LogicException("Could not match empty string...");
        }
        $e = $this->expression(0);
        if ($this->has_current_token()) {
            throw new \LogicException("Could not match complete string...");
        }
        return $e;
    }

    /**
     * Standard procedure to parse an expression.
     *
     * @return mixed
     */
    protected function expression(int $right_binding_power) {
        $s = $this->current_symbol();
        $m = $this->current_match();
        $this->next_token();
        $left = $s->null_denotation($m);

        while ($this->has_current_token() && $right_binding_power < $this->current_symbol()->binding_power()) {
            $s = $this->current_symbol();
            $m = $this->current_match();
            $this->next_token();
            $left = $s->left_denotation($left, $m);
        }

        return $left;
    }

    // Factory Methods

    /**
     * Build the Tokenizer.
     */
    public function build_tokenizer() : T\Tokenizer{
        return new T\Tokenizer($this->symbol_table);
    }

    /**
     * Build the SymbolTable
     */
    protected function build_symbol_table() : SymbolTable {
        return new SymbolTable();
    }

    /**
     * Add symbols to the table.
     *
     * @return void
     */
    abstract protected function add_symbols_to(SymbolTable $table);

    // Helpers for actual parsing.

    /**
     * Set the current token to the next token from the tokenizer.
     * 
     * @return void
     */
    protected function next_token() {
        $this->tokens->next();
    }

    /**
     * Check if current token is valid.
     *
     * @return bool
     */
    protected function has_current_token() {
        return $this->tokens->valid();
    }

    /**
     * Get the current symbol.
     */
    protected function current_symbol() : Symbol {
        return $this->tokens->current()->symbol();
    }

    /**
     * Get the current match.
     *
     * @return  string[] 
     */
    protected function current_match() : array {
        return $this->tokens->current()->match();
    }

    /**
     * Advance the tokenizer to the next token if current token
     * was matched by the given symbol.
     *
     * @return void
     */
    protected function advance(Symbol $symbol) {
        if (!$this->is_current_token_matched_by($symbol)) {
            $match = $this->current_match()[0];
            throw new \LogicException(
                "Syntax Error: Expected '{$symbol->regexp()->raw()}', found '$match'");
        }
        $this->next_token();
    }

    /**
     * Check if the current token was matched by the given regexp.
     *
     * @param   string  $regexp
     * @return  bool
     */
    protected function is_current_token_matched_by(Symbol $sym) : bool {
        return $this->tokens->current()->symbol() == $sym;
    }
}
