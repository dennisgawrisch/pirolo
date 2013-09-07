<?php
namespace Pirolo;

class TextNode extends Node {
    public $parseContents = FALSE;
    public $text;

    public function __construct($text) {
        $this->text = $text;
    }

    public function output($unindent = FALSE) {
        $output = "";
        if (!empty($this->text)) {
            $output .= $unindent ? $this->parent->outputIndent() : $this->outputIndent();
            $output .= $this->text;
            $output .= PHP_EOL;
        }
        foreach ($this->children as $child) {
            $output .= $child->output($unindent || empty($this->text));
        }
        return $output;
    }
}