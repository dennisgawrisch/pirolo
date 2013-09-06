<?php
namespace Pirolo;

class ElementNode extends NodeWithBeginEnd {
    public $name;
    public $text;

    public function __construct($name) {
        $this->name = $name;
    }

    public function outputBegin() {
        $output = "<";
        $output .= $this->name;
        $output .= ">";
        if (!empty($this->text)) {
            $output .= $this->text;
        }
        return $output;
    }

    public function outputEnd() {
        return "</" . $this->name . ">";
    }
}