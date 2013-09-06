<?php
namespace Pirolo;

class XmlDeclarationNode extends CommentNode {
    public function outputBegin() {
        return "<?" . $this->text;
    }

    public function outputEnd() {
        return "?>";
    }
}