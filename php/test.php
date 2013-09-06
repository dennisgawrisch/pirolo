#!/usr/bin/env php
<?php
error_reporting(E_ALL);
set_error_handler(function($level, $message, $filename, $line) {
    throw new ErrorException($message, 0, $level, $filename, $line);
});

spl_autoload_register(function($class) {
    if ("Pirolo\\" == substr($class, 0, 7)) {
        require_once __DIR__ . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $class) . ".php";
    }
});

$cases = array();

$file = fopen(__DIR__ . "/../cases.txt", "rb");
$currentCase = NULL;
$currentlyReading = NULL;
while (!feof($file)) {
    $line = fgets($file);
    if ("" == trim($line)) {
        continue;
    } elseif (preg_match("/^Name: (.+)/", $line, $matches)) {
        if (isset($currentCase)) {
            $cases []= $currentCase;
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
    $cases []= $currentCase;
}

$passCount = 0;
$failCount = 0;
$errorCount = 0;
foreach ($cases as $i => $case) {
    try {
        $markup = new Pirolo\Markup;
        $output = $markup->process($case["input"]);
        if ($case["output"] == $output) {
            ++$passCount;
            echo ".";
        } else {
            ++$failCount;
            echo "F";
            $cases[$i]["fail"] = TRUE;
            $cases[$i]["producedOutput"] = $output;
        }
    } catch (Exception $exception) {
        ++$errorCount;
        echo "E";
        $cases[$i]["error"] = TRUE;
        $cases[$i]["exception"] = $exception;
    }
}
echo PHP_EOL;
echo "Pass: $passCount, fail: $failCount, error: $errorCount" . PHP_EOL;

foreach ($cases as $case) {
    if (!empty($case["fail"])) {
        echo PHP_EOL;
        echo "Failed: " . $case["name"] . PHP_EOL;
        echo "Input:" . PHP_EOL . $case["input"];
        echo "Expected output:" . PHP_EOL . $case["output"];
        echo "Produced output:" . PHP_EOL . $case["producedOutput"] . PHP_EOL;
    }
}

foreach ($cases as $case) {
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
