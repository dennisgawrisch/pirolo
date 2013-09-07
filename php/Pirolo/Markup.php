<?php
namespace Pirolo;

class Markup {
    private $tabReplacementSpaces = 4;
    private $voidElements = array(
        // HTML Living Standard by WHATWG
        // http://www.whatwg.org/specs/web-apps/current-work/#void-elements
        "area", "base", "br", "col", "embed", "hr", "img", "input", "keygen", "link", "menuitem", "meta", "param",
        "source", "track", "wbr",

        // HTML 5
        // http://www.w3.org/TR/html5/syntax.html#void-elements - W3C Candidate Recommendation
        // http://www.w3.org/html/wg/drafts/html/CR/syntax.html#void-elements - W3C Editor’s Draft
        "area", "base", "br", "col", "embed", "hr", "img", "input", "keygen", "link", "meta", "param", "source",
        "track", "wbr",

        // HTML: The Markup Language (an HTML language reference)
        // W3C Working Group Note - discontinued
        // http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
        "area", "base", "br", "col", "command", "embed", "hr", "img", "input", "keygen", "link", "meta", "param",
        "source", "track", "wbr",

        // HTML 4.01 Specification
        // http://www.w3.org/TR/html4/sgml/dtd.html - marked EMPTY
        "br", "area", "link", "img", "param", "hr", "input", "col", "base", "meta",
    );

    public function process($source) {
        // normalize line breaks
        $source = preg_replace("/\\R/", PHP_EOL, $source);

        // replace tabs with spaces
        $source = str_replace("\t", str_repeat(" ", $this->tabReplacementSpaces), $source);

        $document = new DocumentNode;
        $node = $document;

        foreach (explode(PHP_EOL, $source) as $line) {
            // trailing whitespace has no meaning
            $line = rtrim($line);

            // skip empty lines
            if ("" == $line) {
                continue;
            }

            // calculate leading spaces
            for ($leadingSpaces = 0; " " == $line[$leadingSpaces]; $leadingSpaces++);
            $line = substr($line, $leadingSpaces);

            while ($leadingSpaces <= $node->leadingSpaces) {
                $node = $node->parent;
            }
            if ($node->parseContents) {
                if ("|" == $line[0]) {
                    $newNode = new TextNode(ltrim(substr($line, 1)));
                } elseif ("!" == $line[0]) {
                    $line = substr($line, 1);
                    if ("!" == $line[0]) {
                        $newNode = new HiddenNode(ltrim(substr($line, 1)));
                    } elseif (preg_match("/^doctype/i", $line)) {
                        $newNode = new DoctypeNode($line);
                    } elseif (preg_match("/^xml\\s/i", $line)) {
                        $newNode = new XmlDeclarationNode($line);
                    } else {
                        $newNode = new CommentNode(ltrim($line));
                    }
                } elseif (preg_match("/^([^ #\\.]*)(([#\\.][^ #\\.]+)*)((\\s+[^\\|]+(=.+)?)*)( \\| (.+))?$/", $line, $matches)) {
                    $elementName = !empty($matches[1]) ? $matches[1] : "div";
                    $newNode = new ElementNode($elementName);
                    if (in_array($elementName, $this->voidElements)) {
                        $newNode->void = TRUE;
                    }
                    if (!empty($matches[2])) {
                        #TODO $matches[2] — #foo.bar.baz
                    }
                    if (!empty($matches[4])) {
                        $newNode->attributes = $matches[4];
                    }
                    if (!empty($matches[8])) {
                        $newNode->text = $matches[8];
                    }
                }
            } else {
                $newNode = new TextNode($line);
            }
            $newNode->leadingSpaces = $leadingSpaces;
            $newNode->setParent($node);
            $node = $newNode;
        }

        return $document->output();
    }
}