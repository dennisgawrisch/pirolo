<?php
namespace Pirolo;

class ElementNode extends NodeWithBeginEnd {
    public $name;
    public $attributes;
    public $text;
    public $void = FALSE;

    public function __construct($name) {
        $this->name = $name;
    }

    public function outputBegin() {
        $output = "<";
        $output .= $this->name;
        if (!empty($this->attributes)) {
            $output .= $this->attributes;
        }
        if ($this->isVoid()) {
            $output .= "/>";
        } else {
            $output .= ">";
            if (!empty($this->text)) {
                $output .= $this->text;
            }
        }
        return $output;
    }

    public function outputEnd() {
        return $this->isVoid() ? "" : "</" . $this->name . ">";
    }

    protected function isVoid() {
        return $this->void && empty($this->text) && !$this->hasRealChildren();
    }
}