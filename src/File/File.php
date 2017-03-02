<?php

namespace Phase\Finch\File;

use Closure;
use PhpParser\ParserFactory;
use Phase\Finch\ErrorManager;
use Phase\Finch\Console\Message;
use PhpParser\Error as ParsingError;

/**
 * All rules that can be runned on specific file
 */
class File extends FileAttributes
{
    protected $namespace;
    /**
     * Prepare file for analyze
     * @param string $contents Contents of file
     * @param string $name     File name
     * @param array  $rules    Rules that will be applied on file
     */
    public function __construct(string $contents, string $name, array $rules)
    {
        $this->contents = $contents;
        $this->rules = $rules;
        $this->name = $name;
        $this->error = new ErrorManager();
        $this->fileArray = file($name);

        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);

        try {
            $this->stmts = $parser->parse($contents);
        } catch (ParsingError $e) {
            $this->error->setError($this->name, $e->getMessage());
        }
    }
    /**
     * Checkoing line length
     * @param  mixed $stmt
     * @param  string $loglevel
     * @param  mixed $value
     * @return void
     */
    public function lineLength($stmt, string $loglevel, $value)
    {
        $lineLength = strlen($this->getLine($stmt->getAttribute('startLine')));

        if($lineLength > $value) {
            $this->error->set(
                $this->name,
                "line lenght exceeded: Line " . $stmt->getAttribute('startLine'),
                $loglevel
            );
        }

    }
    /**
     * Force namespace to be used for class
     * @param  mixed $stmt
     * @param  string $loglevel
     * @return void
     */
    public function forceNamespace($stmt, string $loglevel)
    {
        if($this->isNamespace($this->stmts[0])) {
            $this->namespace = implode("\\", $this->stmts[0]->name->parts);
        }

        if(is_null($this->namespace)) {
            $this->error->set(
                $this->name,
                "Namespace not found",
                $loglevel
            );


        }
    }
    /**
     * Forbid usage of "else if" statements
     * @param  mixed $stmt
     * @param  string $loglevel
     * @return void
     */
    public function elseif($stmt, string $loglevel)
    {
      if(property_exists($stmt, "elseifs") && count($stmt->elseifs) > 0) {
          foreach($stmt->elseifs as $elseif) {
              $this->error->set(
                  $this->name,
                  "elseif statement is forbiden: Line " . $elseif->getAttribute("startLine"),
                  $loglevel
              );
          }
      }
    }
    /**
     * Forbid usage of "else" statements
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function else($stmt, string $loglevel)
    {
        if(property_exists($stmt, "else") && ! is_null($stmt->else)) {
            $line = $stmt->else->getAttribute("startLine");
            $this->error->set(
                $this->name,
                "else statement is forbiden: Line " . $line,
                $loglevel
            );
        }
    }
    /**
     * Forbid usage of "goto"
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function goto($stmt, string $loglevel)
    {
        if($this->isGoto($stmt)) {
            $this->error->set(
                $this->name,
                "goto statement is forbiden: Line " . $stmt->getAttribute('startLine'),
                $loglevel
            );
        }
    }
    /**
     * Forbid usage of inline brackets for statements, declarations, etc
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function forbidInlineBracket($stmt, string $loglevel)
    {
        if($this->isClass($stmt)) {
            $classString = $this->getLine($stmt->getAttribute('startLine'));
            $openBracketToken = trim($classString)[-1];

            if($openBracketToken == "{") {
                $this->error->set(
                    $this->name,
                    "opening brace for class must start in new line: Line " . $stmt->getAttribute('startLine'),
                    $loglevel
                );
            }
        }

        if($this->isClassMethod($stmt)) {
            $classString = $this->getLine($stmt->getAttribute('startLine'));
            $openBracketToken = trim($classString)[-1];

            if($openBracketToken == "{") {
                $this->error->set(
                    $this->name,
                    "opening brace for class method must start in new line: Line " . $stmt->getAttribute('startLine'),
                    $loglevel
                );
            }
        }
    }
    /**
     * Checking for bubble sort of use statements
     * @param  mixed $stmt
     * @param  string $loglevel
     * @return void
     */
    public function useBubbleSort($stmt, $loglevel)
    {
        if($this->isUse($stmt)) {
            $lineLength = strlen($this->getLine($stmt->getAttribute('startLine')));
            $nextLine = $this->getLine($stmt->getAttribute('startLine') + 1);

            if(substr(trim($nextLine), 0, strlen(self::USE_DECLARATION)) == self::USE_DECLARATION) {
                $nextLineLength = strlen($nextLine);

                if($nextLineLength < $lineLength) {
                    $this->error->set(
                        $this->name,
                        "use statement bubble sort. Line " . $stmt->getAttribute('startLine'),
                        $loglevel
                    );
                }
            }
        }
    }
    /**
     * Forbid usage of "eval"
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function eval($stmt, string $loglevel)
    {
        if($this->isEval($stmt)) {
            $this->error->set(
                $this->name,
                "eval function is forbiden: Line " . $stmt->getAttribute('startLine'),
                $loglevel
            );
        }
    }
    /**
     * Forbid usage of referenced variables
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function varByRef($stmt, string $loglevel)
    {
        if($this->isClassMethod($stmt)) {
            foreach($stmt->params as $param) {
                if($param->byRef) {
                    $this->error->set(
                        $this->name,
                        "passing variables by reference is forbiden: Line " . $stmt->getAttribute('startLine'),
                        $loglevel
                    );
                }
            }
        }
    }
    /**
     * Forbid usage of functions to return reference
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function returnByRef($stmt, string $loglevel)
    {
        if($this->isClassMethod($stmt) && $stmt->byRef) {
            $this->error->set(
                $this->name, "returning by reference is forbiden: Line " . $stmt->getAttribute('startLine'),
                $loglevel
            );
        }
    }
    /**
     * Forbid usage of globals
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function globals($stmt, string $loglevel)
    {
        if($this->isAssign($stmt)) {
            if(property_exists($stmt,"expr") &&  property_exists($stmt->expr,"var") && $stmt->expr->var->name == "GLOBALS") {
                $this->error->set(
                    $this->name,
                    "using globals is forbiden: Line " . $stmt->expr->var->getAttribute('startLine'),
                    $loglevel
                );
            }
        }
    }
    /**
     * Forbids usage of spaces for indentation
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function indentSpace($stmt, string $loglevel)
    {
        if(preg_match("/[\t+]/", $this->getLine($stmt->getAttribute('startLine')))) {
            $this->error->set(
                $this->name,
                "using tabs for indentation is not allowed: Line " . $stmt->getAttribute('startLine'),
                $loglevel
            );
        }
    }

    /**
     * Check for php closing tag
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function phpFileClosingTag($stmt, string $loglevel)
    {
        if(preg_match("/[?]>/", end($this->fileArray))) {
            $this->error->set(
                $this->name,
                "closing file tag ('?>') is forbiden",
                $loglevel
            );
        }
    }

    /**
     * Checks class names for StudlyCaps
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function classStudlyCaps($stmt, string $loglevel)
    {
        if($this->isClass($stmt)) {
          if(preg_match("/([a-z])/", $stmt->name[0]) || preg_match("/[_]/", $stmt->name)) {
              $this->error->set(
                  $this->name,
                  "class names must follow rules for StudlyCaps naming: Line " . $stmt->getAttribute('startLine'),
                  $loglevel
              );
          }
        }
    }
    /**
     * Checks methods name for camelCase naming
     * @param  mixed $stmt
     * @param string $loglevel
     * @return void
     */
    public function methodsCamelCase($stmt, string $loglevel)
    {
        if($this->isClassMethod($stmt)) {
            if(preg_match("/[A-Z]/", $stmt->name[0]) || preg_match("/[_]/", $stmt->name)) {
                if( ! $this->isMagicMethod($stmt->name)) {
                    $this->error->set(
                        $this->name,
                        "method names must follow rules for camelCase naming: Line " . $stmt->getAttribute('startLine'),
                        $loglevel
                    );
                }
            }
        }
    }
    /**
     * Checks code for usage of var_dump function
     * @param  mixed $stmt
     * @param  string $loglevel
     * @return void
     */
    public function vardump($stmt, string $loglevel)
    {
        if($this->isFunc($stmt) && $stmt->name->parts[0] == "var_dump") {
            $this->error->set(
                $this->name,
                "var_dump used: Line " . $stmt->getAttribute('startLine'),
                $loglevel
            );
        }
    }
    /**
     * Iterates over statements
     * @param  mixed  $stmts
     * @param  Closure $cb
     * @return void
     */
    protected function stmt($stmts, Closure $cb)
    {
        foreach($stmts as $stmt) {
            $cb($stmt);
            if(property_exists($stmt,"stmts")) {
                if(count($stmt->stmts) > 0) {
                    $this->stmt($stmt->stmts, $cb);
                }
            }
        }
    }

    /**
     * Start analyzing
     * @param  array  $options
     * @return ErrorManager
     */
    public function analyze(array $options) : ErrorManager
    {
        $this->options = $options;

        $this->stmt($this->stmts, function($stmt) {
            foreach($this->rules as $rule => $value) {
                $r = explode(".", $rule);

                // compatibility with previous versions
                // all rules defaults to errors
                // adding defining errors and warnings
                // is added from 2.2.0 version
                if(method_exists($this,$r[0])) {
                    if($value && ! is_null($this->stmts)) {
                        call_user_func(array($this,$r[0]), $stmt, "error", $value);
                    }

                } else {
                    if(method_exists($this,$r[1])) {
                        if($value && ! is_null($this->stmts)) {
                            call_user_func(array($this,$r[1]), $stmt, $r[0], $value);

                        }
                    }
                }

            }
        });

        return $this->error;
    }


}
