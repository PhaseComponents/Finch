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
     * Object for collecting errors
     * @var phase\Finch\ErrorManager
     */
    public $error;

    public function isNamespace($type) : bool {
        if($type instanceof \PhpParser\Node\Stmt\Namespace_) {
            return true;
        } else {
            return false;
        }
    }
 }
