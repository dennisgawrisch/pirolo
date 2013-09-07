<?php
namespace Pirolo;

class HiddenNode extends Node {
    public $parseContents = FALSE;

    public function output() {
        return "";
    }
}