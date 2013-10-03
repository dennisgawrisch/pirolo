<?php
namespace Pirolo;

use BadMethodCallException;
use RuntimeException;

class CommandLine {
    private $command = "COMMAND";
    private $supportedOptions = array();
    private $argumentName = "ARG";
    private $minArgumentsCount;
    private $maxArgumentsCount;
    private $options = array();
    private $arguments = array();

    public function __construct(array $supportedOptions) {
        $this->setSupportedOptions($supportedOptions);
    }

    public function setSupportedOptions(array $supportedOptions) {
        foreach ($supportedOptions as $definition => $description) {
            $acceptsValue = FALSE;
            $acceptsMultipleValues = FALSE;
            $requiresValue = FALSE;

            $lastChar = substr($definition, -1);
            if (("-" == $lastChar) || ("=" == $lastChar) || ("*" == $lastChar) || ("+" == $lastChar)) {
                $definition = substr($definition, 0, -1);
                $acceptsValue = TRUE;
                $acceptsMultipleValues = (("*" == $lastChar) || ("+" == $lastChar));
                $requiresValue = (("=" == $lastChar) || ("+" == $lastChar));
            }

            $aliases = explode("|", $definition);
            $canonicalName = array_shift($aliases);

            $this->supportedOptions[$canonicalName] = compact("aliases", "description", "acceptsValue", "acceptsMultipleValues", "requiresValue");
            foreach ($aliases as $alias) {
                $this->supportedOptions[$alias] = $canonicalName;
            }
        }
        return $this;
    }

    public function setArgumentName($value) {
        $this->argumentName = $value;
        return $this;
    }

    public function setMinArgumentsCount($value) {
        $this->minArgumentsCount = $value;
        return $this;
    }

    public function setMaxArgumentsCount($value) {
        $this->maxArgumentsCount = $value;
        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    public function getArguments() {
        return $this->arguments;
    }

    public function getOption($option) {
        $option = $this->getCanonicalOptionName($option);
        return isset($this->options[$option]) ? $this->options[$option] : NULL;
    }

    public function process(array $argv = NULL) {
        if (is_null($argv)) {
            if (isset($_SERVER["argv"])) {
                $argv = $_SERVER["argv"];
                $this->command = array_shift($argv);
            } else {
                throw new BadMethodCallException("No argv");
            }
        }

        try {
            $this->options = array();
            $this->arguments = array();

            $forceArguments = FALSE;
            $lastOption = NULL;
            foreach ($argv as $arg) {
                if ($forceArguments) {
                    $this->addArgument($arg);
                } else {
                    if ("-" == $arg[0]) {
                        if ("-" == $arg[1]) {
                            if (2 == strlen($arg)) {
                                $forceArguments = TRUE;
                            } else { // long option
                                $arg = substr($arg, 2);
                                if (1 == strlen($arg)) { // single-char options must use one dash
                                    throw new RuntimeException(sprintf("Use '-%s' instead of '--%1\$s'", $arg));
                                }
                                $equalsSignPos = strpos($arg, "=");
                                if (FALSE === $equalsSignPos) {
                                    $option = $arg;
                                    $this->setOption($option);
                                } else {
                                    $option = substr($arg, 0, $equalsSignPos);
                                    $value = substr($arg, $equalsSignPos + 1);
                                    $this->setOption($option, $value);
                                }
                                $lastOption = $option;
                            }
                        } else { // short option(s)
                            for ($i = 1; $i < strlen($arg); $i++) {
                                $option = $arg[$i];
                                $this->setOption($option);
                                $lastOption = $option;
                            }
                        }
                    } else {
                        if (!empty($lastOption) && $this->optionAcceptsValue($lastOption)) { // option value
                            $this->setOption($lastOption, $arg);
                        } else { // argument
                            $this->addArgument($arg);
                        }
                        $lastOption = NULL;
                    }
                }
            }

            foreach ($this->getOptions() as $option => $value) {
                if ((TRUE === $value) && $this->optionRequiresValue($option)) {
                    throw new RuntimeException(sprintf("Option '%s' requires a value", $option));
                }
            }

            if (!is_null($this->minArgumentsCount) && (count($this->getArguments()) < $this->minArgumentsCount)) {
                throw new RuntimeException(sprintf("Not enough arguments, minimum %d required", $this->minArgumentsCount));
            }
            if (!is_null($this->maxArgumentsCount) && (count($this->getArguments()) > $this->maxArgumentsCount)) {
                throw new RuntimeException(sprintf("Too many arguments, maximum %d allowed", $this->maxArgumentsCount));
            }
        } catch (RuntimeException $exception) {
            $this->echoUsageInfo($exception->getMessage());
            exit($exception->getCode() ?: 1);
        }

        return $this;
    }

    private function addArgument($value) {
        $this->arguments []= $value;
        return $this;
    }

    private function getCanonicalOptionName($option) {
        if (isset($this->supportedOptions[$option])) {
            return is_string($this->supportedOptions[$option]) ? $this->getCanonicalOptionName($this->supportedOptions[$option]) : $option;
        } else {
            throw new RuntimeException(sprintf("Unknown option: '%s'", $option));
        }
    }

    private function optionAcceptsValue($option) {
        $option = $this->getCanonicalOptionName($option);
        return $this->supportedOptions[$option]["acceptsValue"];
    }

    private function optionAcceptsMultipleValues($option) {
        $option = $this->getCanonicalOptionName($option);
        return $this->supportedOptions[$option]["acceptsMultipleValues"];
    }

    private function optionRequiresValue($option) {
        $option = $this->getCanonicalOptionName($option);
        return $this->supportedOptions[$option]["requiresValue"];
    }

    private function setOption($option, $value = TRUE) {
        $option = $this->getCanonicalOptionName($option);
        if ((TRUE !== $value) && !$this->optionAcceptsValue($option)) {
            throw new RuntimeException(sprintf("Option '%s' cannot have value", $option));
        }
        if (isset($this->options[$option]) && (TRUE !== $this->options[$option])) {
            if (!$this->optionAcceptsMultipleValues($option)) {
                throw new RuntimeException(sprintf("Option '%s' cannot have multiple values", $option));
            } else {
                if (!is_array($this->options[$option])) {
                    $this->options[$option] = array($this->options[$option]);
                }
                $this->options[$option] []= $value;
            }
        } else {
            $this->options[$option] = $value;
        }
        return $this;
    }

    public function echoUsageInfo($errorMessage = NULL) {
        if (!empty($errorMessage)) {
            fwrite(STDERR, $errorMessage . PHP_EOL . PHP_EOL);
        }

        $commandLineString = "Usage: {$this->command}";
        if (count($this->supportedOptions) > 0) {
            $commandLineString .= " [OPTION]...";
        }

        $minArgumentsCount = is_null($this->minArgumentsCount) ? 0 : $this->minArgumentsCount;
        $maxArgumentsCount = is_null($this->maxArgumentsCount) ? 100500 : $this->maxArgumentsCount;
        if ($minArgumentsCount > 0) {
            if ($minArgumentsCount <= 3) {
                $commandLineString .= str_repeat(" " . $this->argumentName, $this->minArgumentsCount);
                if ($maxArgumentsCount > $minArgumentsCount) {
                    if ($maxArgumentsCount - $minArgumentsCount <= 3) {
                        $commandLineString .= str_repeat(" [" . $this->argumentName . "]", $this->maxArgumentsCount - $this->minArgumentsCount);
                    } else {
                        $commandLineString .= " [" . $this->argumentName . "]...";
                    }
                }
            } else {
                $commandLineString .= " " . $this->argumentName . "...";
            }
        } else {
            if ($maxArgumentsCount <= 3) {
                $commandLineString .= str_repeat(" [" . $this->argumentName . "]", $this->maxArgumentsCount);
            } else {
                $commandLineString .= " [" . $this->argumentName . "]...";
            }
        }

        fwrite(STDERR, $commandLineString . PHP_EOL);

        if (count($this->supportedOptions) > 0) {
            fwrite(STDERR, PHP_EOL . "Options:" . PHP_EOL);
            foreach ($this->supportedOptions as $option => $record) {
                if (!is_string($record)) {
                    $optionString = "  ";
                    foreach ($record["aliases"] as $alias) {
                        $optionString .= ((strlen($alias) > 1) ? "--" : "-") . $alias;
                        $optionString .= ", ";
                    }
                    $optionString .= ((strlen($option) > 1) ? "--" : "-") . $option;
                    if ($record["acceptsValue"]) {
                        if (!$record["requiresValue"]) {
                            $optionString .= "[";
                        }
                        $optionString .= "=VALUE";
                        if (!$record["requiresValue"]) {
                            $optionString .= "]";
                        }
                    }
                    if (strlen($optionString) < 32) {
                        $optionString .= str_repeat(" ", 32 - strlen($optionString));
                    } else {
                        $optionString .= "    ";
                    }
                    $optionString .= $record["description"];
                    fwrite(STDERR, $optionString . PHP_EOL);
                }
            }
        }
    }
}