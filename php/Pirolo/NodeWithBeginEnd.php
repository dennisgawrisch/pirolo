<?php
namespace Pirolo;

abstract class NodeWithBeginEnd extends Node {
    abstract public function outputBegin();
    abstract public function outputEnd();

    public function output() {
        $indent = $this->outputIndent();
        $begin = $this->outputBegin();
        $end = $this->outputEnd();

        $output = $indent . $begin;
        if ($this->hasRealChildren()) {
            $output .= PHP_EOL;
            foreach ($this->children as $child) {
                $output .= $child->output();
            }
            if (!empty($end)) {
                $output .= $indent . $end . PHP_EOL;
            }
        } else {
            $output .= $end . PHP_EOL;
        }
        return $output;
    }
}