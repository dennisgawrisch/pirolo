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
        if ($this->isReallyVoid()) {
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
        return $this->isReallyVoid() ? "" : "</" . $this->name . ">";
    }

    protected function isReallyVoid() {
        return $this->void && empty($this->text) && $this->hasNoRealChildren();
    }

    protected function hasNoRealChildren() {
        foreach ($this->children as $child) {
            if (! $child instanceof HiddenNode) {
                return FALSE;
            }
        }
        return TRUE;
    }
}