<?php

namespace Phase\Finch;

/**
 *  Parsing and loading configuration file for analyzer
 */
class Config {
    /**
     * Configuration keys and values
     * @var array
     */
    public $configuration = [];
    /**
     * Parse file and return configuration
     *
     * @param mixed $path
     * @return array
     */
    public function load( $path ) : array {
        if(is_null($path)) {
            $this->parse(dirname(dirname(__FILE__)) . "/rules.ini");
        } else {
            $this->parse($path);
        }

        return $this->configuration;
    }
    /**
     * Parse file at provided path
     *
     * @param string $file Path to file which to Parse
     * @return void
     */
    private function parse(string $file) {
        $this->configuration = parse_ini_file($file);
    }
}
