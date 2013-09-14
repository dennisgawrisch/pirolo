<?php
namespace Pirolo;

class TestSuite {
    protected $cases = array();

    public function add($filename) {
        $file = fopen($filename, "rb");
        $currentCase = NULL;
        $currentlyReading = NULL;
        while (!feof($file)) {
            $line = fgets($file);
            if ("" == trim($line)) {
                continue;
            } elseif (preg_match("/^Name: (.+)/", $line, $matches)) {
                if (isset($currentCase)) {
                    $this->cases []= $currentCase;
                }
                $currentCase = array(
                    "name" => $matches[1],
                    "input" => "",
                    "output" => "",
                );
                $currentlyReading = NULL;
            } elseif (preg_match("/^Input:/", $line)) {
                $currentlyReading = "input";
            } elseif (preg_match("/^Output:/", $line)) {
                $currentlyReading = "output";
            } else {
                $currentCase[$currentlyReading] .= $line;
            }
        }
        fclose($file);
        if (isset($currentCase)) {
            $this->cases []= $currentCase;
        }
    }

    public function run() {
        $passCount = 0;
        $failCount = 0;
        $errorCount = 0;
        foreach ($this->cases as $i => $case) {
            try {
                $markup = new Markup;
                $output = $markup->process($case["input"]);

                // additional indentation due to cases text format
                $lines = explode(PHP_EOL, $output);
                foreach ($lines as $j => $line) {
                    if (!empty($line)) {
                        $lines[$j] = "    " . $line;
                    }
                }
                $output = implode(PHP_EOL, $lines);

                if ($case["output"] == $output) {
                    ++$passCount;
                    echo ".";
                } else {
                    ++$failCount;
                    echo "F";
                    $this->cases[$i]["fail"] = TRUE;
                    $this->cases[$i]["producedOutput"] = $output;
                }
            } catch (Exception $exception) {
                ++$errorCount;
                echo "E";
                $this->cases[$i]["error"] = TRUE;
                $this->cases[$i]["exception"] = $exception;
            }
        }
        echo PHP_EOL;
        echo "Pass: $passCount, fail: $failCount, error: $errorCount" . PHP_EOL;

        foreach ($this->cases as $case) {
            if (!empty($case["fail"])) {
                echo PHP_EOL;
                echo "Failed: " . $case["name"] . PHP_EOL;
                echo "Input:" . PHP_EOL . $case["input"];
                echo "Expected output:" . PHP_EOL . $case["output"];
                echo "Produced output:" . PHP_EOL . $case["producedOutput"] . PHP_EOL;
            }
        }

        foreach ($this->cases as $case) {
            if (!empty($case["error"])) {
                echo PHP_EOL;
                echo "Error: " . $case["name"] . PHP_EOL;
                $exception = $case["exception"];
                echo get_class($exception) . PHP_EOL;
                if ($exception->getCode()) {
                    echo "Code: " . $exception->getCode() . PHP_EOL;
                }
                if ($exception->getMessage()) {
                    echo $exception->getMessage() . PHP_EOL;
                    echo "File: " . $exception->getFile() . ", line: " .$exception->getLine() . PHP_EOL;
                    echo "Trace:" . PHP_EOL . $exception->getTraceAsString() . PHP_EOL;
                }
            }
        }
    }
}