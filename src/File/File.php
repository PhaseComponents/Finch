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
     * Forbid usage of "else if" statements
     * @param  mixed $value
     * @return void
     */
    public function elseif( $stmt, $attr ) {
      if(property_exists($stmt, "elseifs") && count($stmt->elseifs) > 0) {
          foreach($stmt->elseifs as $elseif) {
              $this->error->setError($this->name, "elseif statement is forbiden: Line " . $elseif->getAttribute("startLine"));
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
            $line = $stmt->else->getAttribute("startLine");
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
    public function varByRef( $stmt, $attr ) {
        if($this->isClassMethod($stmt)) {
            foreach($stmt->params as $param) {
                if($param->byRef) {
                    $line = $param->getAttribute('startLine');
                    $this->error->setError($this->name, "passing variables by reference is forbiden: Line $line");
                }
            }
        }
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
    public function globals( $stmt, $attr ) {
        if($this->isAssign($stmt)) {
            if(property_exists($stmt,"expr") &&  property_exists($stmt->expr,"var") && $stmt->expr->var->name == "GLOBALS") {
                $this->error->setError($this->name, "using globals is forbiden: Line " . $stmt->expr->var->getAttribute('startLine'));
            }
        }
    }
    /**
     * Forbids usage of spaces for indentation
     * @param  mixed $value
     * @return void
     */
    public function indentSpace($value) {
        if(preg_match("/[\t+]/", $this->contents)) {
            $this->error->setError($this->name, "using tabs for indentation is not allowed");
        }
    }

    /**
     * Check for php closing tag
     * @param  mixed $value
     * @return void
     */
    public function phpFileClosingTag() {
        if(preg_match("/[?]>/", end($this->fileArray))) {
            $this->error->setError($this->name, "closing file tag ('?>') is forbiden: Line " . count($this->fileArray));
        }

        return 1;
    }

    /**
     * Checks class names for StudlyCaps
     * @param  mixed $value
     * @return void
     */
    public function classStudlyCaps( $stmt, $attr ) {
        if($this->isClass($stmt)) {
          if(preg_match("/([a-z])/", $stmt->name[0]) || preg_match("/[_]/", $stmt->name)) {
              $this->error->setError($this->name, "class names must follow rules for StudlyCaps naming: Line " . $attr["startLine"]);
          }
        }
    }
    /**
     * Checks methods name for camelCase naming
     * @param  mixed $value
     * @return void
     */
    public function methodsCamelCase( $stmt, $attr ) {
        if($this->isClassMethod($stmt)) {
            if(preg_match("/[A-Z]/", $stmt->name[0]) || preg_match("/[_]/", $stmt->name)) {
                $this->error->setError($this->name, "method names must follow rules for camelCase naming: Line " . $attr["startLine"]);
            }
        }
    }
    /**
     * Iterates over statements
     * @param  mixed  $stmts
     * @param  Closure $cb
     * @return void
     */
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
