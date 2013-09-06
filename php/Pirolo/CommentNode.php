<?php
namespace Pirolo;

class CommentNode extends NodeWithBeginEnd {
    public $parseContents = FALSE;
    public $text;

    public function __construct($text) {
        $this->text = $text;
    }

    public function outputBegin() {
        $output = "<!--";
        if (!empty($this->text)) {
            $output .= " " . $this->text;
        }
        return $output;
    }

    public function outputEnd() {
        $output = "-->";
        if (!empty($this->text) && (0 == count($this->children))) {
            $output = " " . $output;
        }
        return $output;
    }
}