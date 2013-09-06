<?php
namespace Pirolo;

class TextNode extends Node {
    public $parseContents = FALSE;
    public $text;

    public function __construct($text) {
        $this->text = $text;
    }

    public function output() {
        $output = "";
        if (!empty($this->text)) {
            $output .= str_repeat(" ", $this->leadingSpaces);
            $output .= $this->text;
            $output .= PHP_EOL;
        }
        foreach ($this->children as $child) {
            $output .= $child->output();
        }
        return $output;
    }
}