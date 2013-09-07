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
        $parent = $document;

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

            while ($leadingSpaces <= $parent->leadingSpaces) {
                $parent = $parent->parent;
            }

            if (!$parent->parseContents) {
                $node = new TextNode($line);
            } elseif ("|" == $line[0]) {
                $node = new TextNode(ltrim(substr($line, 1)));
            } elseif ("!!" == substr($line, 0, 2)) {
                $node = new HiddenNode;
            } elseif (preg_match("/^!doctype\\s/i", $line)) {
                $node = new DoctypeNode(substr($line, 1));
            } elseif (preg_match("/^!xml\\s/i", $line)) {
                $node = new XmlDeclarationNode(substr($line, 1));
            } elseif ("!" == $line[0]) {
                $node = new CommentNode(ltrim(substr($line, 1)));
            } elseif ("&" == $line[0]) {
                $node = new HiddenNode;
                $parent->attributes .= " " . ltrim(substr($line, 1));
            } else { // this is an element node
                $textDelimiterPos = strpos($line, " | ");
                if (FALSE === $textDelimiterPos) {
                    $text = NULL;
                } else {
                    $text = substr($line, $textDelimiterPos + 3);
                    $line = substr($line, 0, $textDelimiterPos);
                }

                $elementDeclarations = explode(" > ", $line);
                foreach ($elementDeclarations as $i => $line) {
                    $firstSpacePos = strpos($line, " ");
                    if (FALSE === $firstSpacePos) {
                        $attributesString = NULL;
                    } else {
                        $attributesString = substr($line, $firstSpacePos);
                        $line = substr($line, 0, $firstSpacePos);
                    }

                    // element name followed by id & class shorthand specifications
                    preg_match("/([^#\\.]*)((?:[#\\.][^#\\.]+)*)/", $line, $matches);
                    $elementName = empty($matches[1]) ? "div" : $matches[1];
                    if (!empty($matches[2])) {
                        if (preg_match_all("/\\.([^#\\.]+)/", $matches[2], $classMatches)) {
                            $classValue = implode(" ", $classMatches[1]);
                            $classAttributeString = 'class="' . htmlspecialchars($classValue, ENT_QUOTES, "UTF-8") .'"';
                            $attributesString = " " . $classAttributeString . $attributesString;
                        }
                        if (preg_match_all("/#([^#\\.]+)/", $matches[2], $idMatches)) {
                            $idValue = $idMatches[1][count($idMatches[1]) - 1];
                            $idAttributeString = 'id="' . htmlspecialchars($idValue, ENT_QUOTES, "UTF-8") .'"';
                            $attributesString = " " . $idAttributeString . $attributesString;
                        }
                    }

                    $node = new ElementNode($elementName);
                    if (in_array(strtolower($elementName), $this->voidElements)) {
                        $node->void = TRUE;
                    }
                    $node->attributes = $attributesString;

                    if ($i < count($elementDeclarations) - 1) {
                        $node->leadingSpaces = $leadingSpaces;
                        $node->setParent($parent);
                        $parent = $node;
                    }
                }

                // set text for last node
                $node->text = $text;
            }

            $node->leadingSpaces = $leadingSpaces;
            $node->setParent($parent);
            $parent = $node;
        }

        return $document->output();
    }
}