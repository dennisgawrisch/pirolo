<?php
namespace Pirolo;

class DocumentNode extends Node {
    public function __construct() {
        $this->leadingSpaces = -1;
        $this->level = -1;
    }

    public function output() {
        $output = "";
        foreach ($this->children as $child) {
            $output .= $child->output();
        }
        return $output;
    }
}