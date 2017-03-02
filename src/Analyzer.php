<?php

namespace Phase\Finch;

use Phase\Finch\File\File;
use Phase\Finch\ErrorManager;
use Phase\Finch\Console\Message;
use Phase\Finch\File\FileCollection;


class Analyzer extends Options {
    /**
     * Collection of files that will be analyzed
     * @var array $collection
     */
    protected $collection;
    /**
     * Collection of rules that will be used
     * @var array $rules
     */
    protected $rules;
    /**
     * Phase\Finch\ErrorManager
     * @var object
     */
    protected $error;

    /**
     * Create instance of analyzer with provided files, rules and options
     * @param FileCollection $collection
     * @param array          $rules
     * @param array          $options
     */
    public function __construct(FileCollection $collection, array $rules, array $options) {
        $this->collection = $collection->collection;
        $this->rules = $rules;
        $this->options = $options;
        $this->error = new ErrorManager();
    }
    /**
     * Prints basic info
     * @return mixed
     */
    private function _printInfo() {
        if(count($this->collection) < 1) {
            return 0;
        }

        if(isset($this->options["v"])) {
            Message::info("Analyze will be run on following files");

            foreach($this->collection as $item) {
                print $item . "\n";
            }

            Message::info("With following rules");

            foreach($this->rules as $key => $rule) {
                print $key ." -> ". $rule . "\n";
            }


        }

    }

    public function __get( $var ) {
        if(property_exists($this,$var)) {
            return $this->$var;
        } else {
            return NULL;
        }
    }

    /**
     * Run analyzer for provided file collection
     * @param  mixed $rusage
     * @return void
     */
    public function run($rusage) {
        $this->_printInfo();

        foreach($this->collection as $file) {
            if(is_readable($file)) {
                $fp = file_get_contents($file);

                $f = new File($fp, $file, $this->rules);
                $f->analyze($this->options);

                $this->error->collection = array_merge($this->error->collection, $f->error->collection);
                $this->error->errorsCount += $f->error->errorsCount;
                $this->error->warningCount += $f->error->warningCount;

            } else {
                Message::error("$file is not readable.");
            }
        }


        $console = new \Phase\Finch\Report\Console($this->error);
        $console->write();

        // call methods defined from options
        foreach($this->options as $option => $value) {
            if(method_exists($this,$option)) {
                call_user_func(array($this,$option), $value);
            }
        }
    }
}
