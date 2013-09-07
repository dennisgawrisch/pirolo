<?php
namespace Pirolo;

abstract class Node {
    public $parent;
    public $children = array();
    public $leadingSpaces;
    public $level;
    public $parseContents = TRUE;

    public function setParent(Node $parent) {
        $this->parent = $parent;
        $parent->children []= $this;
        $this->level = $parent->level + 1;
        return $this;
    }

    abstract public function output();

    protected function hasRealChildren() {
        foreach ($this->children as $child) {
            if (! $child instanceof HiddenNode) {
                return TRUE;
            }
        }
        return FALSE;
    }

    protected function outputIndent() {
        return str_repeat(" ", 4 * $this->level);
    }
}