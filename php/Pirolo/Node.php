<?php
namespace Pirolo;

abstract class Node {
    public $parent;
    public $children = array();
    public $leadingSpaces;
    public $parseContents = TRUE;

    public function setParent(Node $parent) {
        $this->parent = $parent;
        $parent->children []= $this;
        return $this;
    }

    abstract public function output();
}