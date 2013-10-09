<?php
namespace Pirolo;

class PhpNode extends NodeWithBeginEnd {
    public $text;
    public $parseContents;

    public function __construct($text) {
        $this->text = $text;
        $this->parseContents = !empty($this->text);
    }

    public function outputBegin() {
        $output = "<?php";
        if (!empty($this->text)) {
            $output .= " " . $this->text;
            if (count($this->children) > 0) {
                $output .= " { ?>";
            }
        }
        return $output;
    }

    public function outputEnd() {
        return empty($this->text) ? "?>" : ((count($this->children) > 0) ? "<?php } ?>" : "; ?>");
    }
}