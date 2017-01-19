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
            $this->_parse(dirname(dirname(__FILE__)) . "/rules");
        } else {
            $this->_parse($path);
        }

        return $this->configuration;
    }
    /**
     * Parse file at provided path
     *
     * @param string $file Path to file which to Parse
     * @return void
     */
    private function _parse(string $file) {
        $contents = file_get_contents($file);

        $values = explode("\n", $contents);
        foreach($values as $value) {
            $settings = explode("=", $value);

            if(! empty($settings[0]))
                $this->configuration[$settings[0]] = $settings[1];

        }

    }
}
