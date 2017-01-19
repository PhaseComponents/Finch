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
     * Object for collecting errors
     * @var phase\Finch\ErrorManager
     */
    public $error;

    const START_FILE_TAG = "<?php";
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
 }
