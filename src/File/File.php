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
class File extends FileAttributes {
    /**
     * Prepare file for analyze
     * @param string $contents Contents of file
     * @param string $name     File name
     * @param array  $rules    Rules that will be applied on file
     */
    public function __construct(string $contents, string $name, array $rules) {
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
     * Force namespace to be used for class
     * @param  mixed $value
     * @return void
     */
    public function forceNamespace() {
        if( ! $this->isNamespace($this->stmts[0])) {
            $this->error->setError($this->name, "Namespace not found");
            return 0;
        }

        return 1;
    }
    /**
     * Looks for line length in file
     * @param  mixed $value
     * @return void
     */
    public function lineLength( $stmt, $attr, $value ) {
        $this->fileIterator(function($line, $content) use ($value) {
            if(strlen($content) > $value) {
                $this->error->setError($this->name, "line length exceeded: Line $line");
            }
        });
    }
    /**
     * Forbid usage of "else if" statements
     * @param  mixed $value
     * @return void
     */
    public function elseif( $stmt, $attr ) {
      if(property_exists($stmt, "elseifs") && count($stmt->elseifs) > 0) {
          foreach($stmt->elseifs as $elseif) {
              $this->error->setError($this->name, "elseif statement is forbiden: Line " . $elseif->getAttributes()["startLine"]);
          }
      }

        return 1;
    }
    /**
     * Forbid usage of "else" statements
     * @param  mixed $value
     * @return void
     */
    public function else( $stmt, $attr ) {
        if(property_exists($stmt, "else") && ! is_null($stmt->else)) {
            $line = $stmt->else->getAttributes()["startLine"];
            $this->error->setError($this->name, "else statement is forbiden: Line " . $line);
        }

        return 1;
    }
    /**
     * Forbid usage of "goto"
     * @param  mixed $value
     * @return void
     */
    public function goto( $stmt, $attr ) {
        if($this->isGoto($stmt)) {
            $this->error->setError($this->name, "goto statement is forbiden: Line " . $attr["startLine"]);
        }

        return 1;
    }
    /**
     * Forbid usage of inline brackets for statements, declarations, etc
     * @param  mixed $value
     * @return void
     */
    public function forbidInlineBracket( $stmt, $attr ) {
        if($this->isClass($stmt)) {
            $classString = $this->getLine($attr["startLine"]);
            $openBracketToken = trim($classString)[-1];

            if($openBracketToken == "{") {
                $this->error->setError($this->name, "opening brace for class must start in new line: Line " . $attr["startLine"]);
            }
        }

        if($this->isClassMethod($stmt)) {
            $classString = $this->getLine($attr["startLine"]);
            $openBracketToken = trim($classString)[-1];

            if($openBracketToken == "{") {
                $this->error->setError($this->name, "opening brace for class method must start in new line: Line " . $attr["startLine"]);
            }
        }
    }
    /**
     * Forbid usage of "eval"
     * @param  mixed $value
     * @return void
     */
    public function eval( $stmt, $attr ) {
        if($this->isEval($stmt)) {
            $this->error->setError($this->name, "eval function is forbiden: Line " . $attr["startLine"]);
        }

        return 1;
    }
    /**
     * Forbid usage of referenced variables
     * @param  mixed $value
     * @return void
     */
    public function varByRef() {
      $this->fileIterator(function($line,$content) {
          if(preg_match("/&[$]/i", $content)) {
             $this->error->setError($this->name, "passing by reference is forbiden: Line $line");
          }

          return 1;
      });
    }
    /**
     * Forbid usage of functions to return reference
     * @param  mixed $value
     * @return void
     */
    public function returnByRef( $stmt, $attr ) {
        if($this->isClassMethod($stmt) && $stmt->byRef) {
            $line = $attr["startLine"];
            $this->error->setError($this->name, "returning by reference is forbiden: Line $line");
        }
    }
    /**
     * Forbid usage of globals
     * @param  mixed $value
     * @return void
     */
    public function globals() {
      $this->fileIterator(function($line,$content) {
          if(preg_match("/[$]GLOBALS/i", $content)) {
             $this->error->setError($this->name, "using globals is forbiden: Line $line");
          }

          return 1;
      });
    }
    /**
     * Forbids usage of spaces for indentation
     * @param  mixed $value
     * @return void
     */
    public function indentSpace($value) {
        $this->fileIterator(function($line,$content) use ($value) {
            if(preg_match("/^[\t+]/", $content)) {
                $this->error->setError($this->name, "using tabs for indentation is not allowed: Line $line");
            }

            return 1;

        });
    }
    /**
     * Force use class bubble sorting
     * @param  mixed $value
     * @return void
     */
    public function useClassBubble($value) {
        $this->fileIterator(function($line,$content) use ($value) {
            $content = trim($content);

            if(substr($content, 0, 3) == self::USE_DECLARATION) {
                static $useClassLength = 0;

                if($useClassLength > strlen($content)) {
                    $this->error->setError($this->name, "use class bubble sorting not correct: Line $line");
                }

                if($useClassLength < strlen($content)) {
                    $useClassLength = strlen($content);
                }

            } else if(substr($content, 0, 5) == self::CLASS_DECLARATION) {
                // stop iteration here since we don't count in trait uses
                return 0;
            }

            return 1;
        });
    }

    /**
     * Check for php closing tag
     * @param  mixed $value
     * @return void
     */
    public function phpFileClosingTag($value) {
        $this->fileIterator(function($line,$content) {
            $content = trim($content);

            if(preg_match("/[?]>/", $content) && $line == count($this->contents)) {
                $this->error->setError($this->name, "closing file tag ('?>') is forbiden: Line $line");

            }

            return 1;
        });
    }

    /**
     * Checks class names for StudlyCaps
     * @param  mixed $value
     * @return void
     */
    public function classStudlyCaps($value) {
        $this->fileIterator(function($line, $content) {
            $content = trim($content);
            if(substr($content, 0, strlen(self::CLASS_DECLARATION)) == self::CLASS_DECLARATION) {
                $class = explode(" ", $content);
                $classKey = array_search(self::CLASS_DECLARATION, $class);
                $className = $class[$classKey+1];

                if(preg_match("/([a-z])/", $className[0]) || preg_match("/[_]/", $className)) {
                    $this->error->setError($this->name, "class names must follow rules for StudlyCaps naming: Line $line");

                }
            }

            return 1;
        });
    }
    /**
     * Checks methods name for camelCase naming
     * @param  mixed $value
     * @return void
     */
    public function methodsCamelCase($value) {
        $this->fileIterator(function($line, $content) {
            $content = trim($content);
            if(preg_match("/(p.*) function/i", $content)) {
                $splitLine = explode(" ", $content);
                $key = array_search(self::FUNCTION_DECLARATION, $splitLine);
                $methodKey = $key + 1;
                $methodName = $splitLine[$methodKey];

                if(preg_match("/[A-Z]/", $methodName[0]) || preg_match("/[_]/", $content)) {
                    $this->error->setError($this->name, "method names must follow rules for camelCase naming: Line $line");
                }

            }

            return 1;
        });
    }

    protected function stmt($stmts, Closure $cb) {
        foreach($stmts as $stmt) {
            if(property_exists($stmt,"stmts")) {
                if(count($stmt->stmts) > 0) {
                    $cb($stmt->stmts);
                    $this->stmt($stmt->stmts, $cb);
                }
            }
        }
    }

    protected function fileIterator(Closure $cb) {
        foreach($this->fileArray as $line => $content) {
            $cb(($line + 1), $content);
        }
    }

    /**
     * Start analyzing
     * @param  array  $options
     * @return void
     */
    public function analyze(array $options) {
        $this->options = $options;

        foreach($this->rules as $rule => $value) {
            if(method_exists($this,$rule)) {
                if($value && ! is_null($this->stmts)) {
                    $this->stmt($this->stmts, function($stmt) use ($rule, $value) {
                       call_user_func(array($this,$rule), $stmt[0], $stmt[0]->getAttributes(), $value);
                    });
                }
            }
        }

        return $this->error;
    }


}
