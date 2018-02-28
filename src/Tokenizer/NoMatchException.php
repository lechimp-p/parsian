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

class NoMatchException extends Exception {
    const UNPARSED_PREVIEW = 20;

    /**
     * @var string
     */
    protected $unmatched_text;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var int
     */
    protected $column;

    public function __construct(string $unmatched_text, int $line, int $column) {
        $preview = substr($unmatched_text, 0, self::UNPARSED_PREVIEW);
        parent::__construct("Could not match '$preview'");
        $this->unmatched_text = $unmatched_text;
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * Get the text that could not be matched.
     */
    public function unmatched_text() : string {
        return $this->unmatched_text;
    }

    /**
     * Get the line where unmatched text starts.
     */
    public function line() : int {
        return $this->line;
    }

    /**
     * Get the column where unmatched text starts.
     */
    public function column() : int {
        return $this->column;
    }
} 
