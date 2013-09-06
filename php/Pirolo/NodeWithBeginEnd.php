<?php
namespace Pirolo;

abstract class NodeWithBeginEnd extends Node {
    abstract public function outputBegin();
    abstract public function outputEnd();

    public function output() {
        $output = str_repeat(" ", $this->leadingSpaces);
        $output .= $this->outputBegin();
        if (count($this->children) > 0) {
            $output .= PHP_EOL;
            foreach ($this->children as $child) {
                $output .= $child->output();
            }
            $output .= str_repeat(" ", $this->leadingSpaces);
        }
        $output .= $this->outputEnd();
        $output .= PHP_EOL;
        return $output;
    }
}