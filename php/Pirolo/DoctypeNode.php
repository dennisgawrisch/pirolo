<?php
namespace Pirolo;

class DoctypeNode extends CommentNode {
    public function outputBegin() {
        return "<!" . $this->text;
    }

    public function outputEnd() {
        return ">";
    }
}