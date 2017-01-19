<?php

namespace Phase\Finch\File;

use Closure;
use Phase\Finch\ErrorManager;
use Phase\Finch\Console\Message;

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
    public function __construct(array $contents, string $name, array $rules) {
        $this->contents = $contents;
        $this->rules = $rules;
        $this->name = $name;
        $this->error = new ErrorManager();
    }
    /**
     * Force namespace to be used for class
     * @param  mixed $value
     * @return void
     */
    public function forceNamespace( $value ) {
        $this->fileIterator(function($line,$content) use ($value, $namespace) {
          static $namespace = 0;

          if(substr(trim($content),0,strlen(self::NAMESPACE_DECLARATION)) == self::NAMESPACE_DECLARATION) {
              $namespace = 1;
          }

          if(substr(trim($content),0,strlen(self::CLASS_DECLARATION)) == self::CLASS_DECLARATION) {
              if( ! $namespace) {
                  $this->error->setError($this->name, "Namespace not found");
                  return 0;
              }
          }

          return 1;
        });
    }
    /**
     * Looks for line length in file
     * @param  mixed $value
     * @return void
     */
    public function lineLength( $value ){
        $this->fileIterator(function($line,$content) use ($value) {
            if(strlen($content) >= $value) {
              $this->error->setError($this->name, "Line length exceeded : Line $line");
            }

            return 1;
        });
    }
    /**
     * Forbid usage of "else if" statements
     * @param  mixed $value
     * @return void
     */
    public function elseif( $value ) {
        $this->fileIterator(function($line,$content) use ($value){
          if(preg_match("/else if/i", $content)) {
              $this->error->setError($this->name, "else if statement is forbiden: Line $line");
          }

          return 1;
        });
    }
    /**
     * Forbid usage of "else" statements
     * @param  mixed $value
     * @return void
     */
    public function else( $value ) {
      $this->fileIterator(function($line,$content) use ($value){
          if(preg_match("/(else[\\s]?[{])|(else[\\n])/i", $content)) {
              $this->error->setError($this->name, "else statement is forbiden: Line $line");
          }

          return 1;
      });
    }
    /**
     * Forbid usage of "goto"
     * @param  mixed $value
     * @return void
     */
    public function goto( $value ) {
      $this->fileIterator(function($line,$content) use ($value){
          if(preg_match("/goto (.*)/i", $content)) {
              $this->error->setError($this->name, "goto statement is forbiden: Line $line");
          }

          return 1;
      });
    }
    /**
     * Forbid usage of inline brackets for statements, declarations, etc
     * @param  mixed $value
     * @return void
     */
    public function forbidInlineBracket( $value ) {
        $this->fileIterator(function($line,$content) use ($value) {
            $content = trim($content);
            $last_char = substr($content, -1);

            if($last_char == self::STATEMENT_OPENING_BRACKET && strlen($content) != 1) {
                $this->error->setError($this->name, "have inline opening bracket: Line $line");
            }

            return 1;
        });
    }
    /**
     * Forbid usage of "eval"
     * @param  mixed $value
     * @return void
     */
    public function eval() {
      $this->fileIterator(function($line,$content) {
          if(preg_match("/eval[(].*[)]/i", $content)) {
             $this->error->setError($this->name, "have forbiden function eval(): Line $line");
          }

          return 1;
      });
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
    public function returnByRef() {
      $this->fileIterator(function($line,$content) {
          if(preg_match("/function &(.*)/i", $content)) {
             $this->error->setError($this->name, "returning by reference is forbiden: Line $line");
          }

          return 1;
      });
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

    /**
     * Iterating through file content
     * @param  Closure $cb
     * @return void
     */
    protected function fileIterator(Closure $cb) {
      foreach($this->contents as $line => $content) {
          // if function returns 0 atleast once, we stop to call it
          $r = $cb(($line+1),$content);

          if( ! $r) {
              break;
          } else {
              continue;
          }
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
                if($value) {
                    call_user_func(array($this,$rule), $value);
                }
            }
        }

        return $this->error;
    }


}
