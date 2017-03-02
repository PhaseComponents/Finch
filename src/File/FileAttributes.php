<?php

namespace Phase\Finch\File;

abstract class FileAttributes {
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
    /**
     * List of magic methods
     * @var array
     */
    protected $magicMethods = [
        "__construct",
        "__destruct",
        "__get",
        "__set",
        "__sleep",
        "__wakeup",
        "__invoke",
        "__call",
        "__callStatic",
        "__isset",
        "__unset",
        "__toString",
        "__set_state",
        "__clone",
        "__debugInfo"
    ];

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
    /**
     * Get requested line of code
     * @param  int    $line
     * @return string
     */
    public function getLine(int $line) : string {
        return $this->fileArray[$line - 1];
    }
    /**
     * Check is statement a namespace
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isNamespace($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Namespace_) ? true : false;
    }
    /**
     * Check is statement a class
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isClass($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Class_) ? true : false;
    }
    /**
     * Check is statement a class method
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isClassMethod($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\ClassMethod) ? true : false;
    }
    /**
     * Check is statement "if"
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isIf($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\If_) ? true : false;
    }
    /**
     * Check is statement "else"
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isElse($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Else_) ? true : false;
    }
    /**
     * Check is statement "goto"
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isGoto($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Goto_) ? true : false;
    }
    /**
     * Check is expression "eval"
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isEval($type) : bool {
       return ($type instanceof \PhpParser\Node\Expr\Eval_) ? true : false;
    }
    /**
     * Check is statement "assign"
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isAssign($type) : bool {
        return ($type instanceof \PhpParser\Node\Expr\Assign) ? true : false;
    }
    /**
     * Check is statement "use"
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isUse($type) : bool {
        return ($type instanceof \PhpParser\Node\Stmt\Use_) ? true : false;
    }
    /**
     * Check is expression function
     * @param  mixed $type Passed instance of object
     * @return bool
     */
    public function isFunc($type) : bool {
        return ($type instanceof \PhpParser\Node\Expr\FuncCall) ? true : false;
    }

    public function isMagicMethod(string $name) : bool {
        return in_array($name, $this->magicMethods);
    }
 }
