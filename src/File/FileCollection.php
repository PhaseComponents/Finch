<?php

namespace Phase\Finch\File;

use Phase\Finch\Console\Message;

class FileCollection {
    /**
     * Collection of files that will be analyzed
     * @var [type]
     */
    public $collection = [];

    /**
     *  Start collecting
     * @param string $path Provided path to dir
     */
    public function __construct(string $path) {
        if(is_dir($path)) {
            // proceed with dir
            $this->collectFromDir($path);
        } else {
            if(is_readable($path)) {
                // proceed with file
                $this->collectFile($path);
            } else {
                Message::error("{$path} doesn't exist or it isn't readable!");
            }

        }
    }
    /**
     * [collectFromDir description]
     * @param  string $path [description]
     * @return [type]       [description]
     */
    public function collectFromDir(string $path) {
        $files = scandir($path);

        foreach($files as $file) {
            if($file !== "." && $file !== "..") {
               if(is_dir($path . "/" . $file)) {
                  $this->collectFromDir($path ."/". $file);
               } else {
                  $this->collectFile($path ."/". $file);
               }
            }
        }
    }

    public function collectFile(string $path) {
        if($this->checkFileExtension($path)) {
            $this->_pushToCollection($path);

        }
    }

    public function checkFileExtension(string $file) : bool {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if($ext === "php") {
            return true;
        } else {
            return false;
        }
    }

    private function _pushToCollection(string $path) {
        $oldLength = count($this->collection);
        $newLength = array_push($this->collection, $path);

        if($oldLength == $newLength) {
            Message::error("$path isn't collected, some error occured!");
        }
    }
}
