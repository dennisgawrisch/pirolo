<?php
namespace Pirolo;

class HiddenCommentNode extends Node {
    public $parseContents = FALSE;

    public function output() {
        return "";
    }
}