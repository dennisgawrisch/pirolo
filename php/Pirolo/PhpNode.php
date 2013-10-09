<?php
namespace Pirolo;

class PhpNode extends NodeWithBeginEnd {
    const TYPE_REGULAR      = 0;
    const TYPE_ECHO         = 1;
    const TYPE_ECHO_ESCAPE  = 2;

    public $text;
    public $parseContents;

    public function __construct($text, $type = self::TYPE_REGULAR) {
        if (self::TYPE_ECHO == $type) {
            $text = "echo $text";
        } elseif (self::TYPE_ECHO_ESCAPE == $type) {
            $text = "echo htmlspecialchars($text)";
        }
        $this->text = $text;
        $this->parseContents = !empty($this->text);
    }

    public function outputBegin() {
        $output = "<?php";
        if (!empty($this->text)) {
            if ($this->isSecondaryPartOfCompoundStatement()) {
                $output .= " } " . $this->text;
            } else {
                $output .= " " . $this->text;
            }
            if (count($this->children) > 0) {
                $output .= " { ?>";
            }
        }
        return $output;
    }

    public function outputEnd() {
        return empty($this->text) ? "?>" : ((count($this->children) > 0) ? ($this->isPrimaryPartOfCompoundStatement() ? "" : "<?php } ?>") : "; ?>");
    }

    public function isPrimaryPartOfCompoundStatement() {
        return $this->nextSibling && (
            (preg_match("/^(if|else)/", $this->text) && preg_match("/^else/", $this->nextSibling->text))
            || (preg_match("/^do/", $this->text) && preg_match("/^while/", $this->nextSibling->text))
            || (preg_match("/^(try|catch)/", $this->text) && preg_match("/^(catch|finally)/", $this->nextSibling->text))
        );
    }

    public function isSecondaryPartOfCompoundStatement() {
        return preg_match("/^(else|catch|finally)/", $this->text)
            || (preg_match("/^while/", $this->text) && $this->previousSibling && preg_match("/^do/", $this->previousSibling->text));
    }

    public function outputInline() {
        return "<?php " . $this->text . "; ?>";
    }
}