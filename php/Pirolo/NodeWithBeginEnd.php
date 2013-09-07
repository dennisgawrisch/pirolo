<?php
namespace Pirolo;

abstract class NodeWithBeginEnd extends Node {
    abstract public function outputBegin();
    abstract public function outputEnd();

    public function output() {
        $output = $this->outputIndent();
        $output .= $this->outputBegin();
        if ($this->hasRealChildren()) {
            $output .= PHP_EOL;
            foreach ($this->children as $child) {
                $output .= $child->output();
            }
            $output .= $this->outputIndent();
        }
        $output .= $this->outputEnd();
        $output .= PHP_EOL;
        return $output;
    }
}