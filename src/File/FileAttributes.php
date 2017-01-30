<?php

namespace Phase\Finch\File;

class FileAttributes {
    /**
     * File name
     * @var string
     */
    protected $name;
    /**
     * Contents of file
     * @var string
     */
    protected $contents;
    /**
     * Rules that will be applied on that specific file
     * @var array
     */
    protected $rules;
    /**
     * Parsed file
     * @var array
     */
    protected $stmts;
    /**
    * Parsed file as array
    * @var Array
    */
    protected $fileArray;
    /**
     * Object for collecting errors
     * @var phase\Finch\ErrorManager
     */
    public $error;

    const START_FILE_TAG = "<?php";
    const SHORT_START_FILE_TAG = "<?";
    const END_FILE_TAG = "?>";
    const CLASS_DECLARATION = "class";
    const USE_DECLARATION = "use";
    const FUNCTION_DECLARATION = "function";
    const NAMESPACE_DECLARATION = "namespace";
    const PRIVATE_METHOD_DECLARATION = "private";
    const PUBLIC_METHOD_DECLARATION = "public";
    const PROTECTED_METHOD_DECLARATION = "protected";
    const STATEMENT_OPENING_BRACKET = "{";
    const STATEMENT_CLOSING_BRACKET = "}";
    const END_OF_CODE_LINE = ";";

    public function getLine(int $line) {
        return $this->fileArray[$line - 1];
    }

    public function isNamespace($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Namespace_) ? true : false;
    }

    public function isClass($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Class_) ? true : false;
    }

    public function isClassMethod($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\ClassMethod) ? true : false;
    }

    public function isIf($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\If_) ? true : false;
    }

    public function isElse($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Else_) ? true : false;
    }

    public function isGoto($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Goto_) ? true : false;
    }

    public function isEval($type) : bool {
       return ($type instanceof PhpParser\Node\Expr\Eval_) ? true : false;
    }

    public function isForeach($type) : bool {

    }
 }
