<?php

namespace Phase\Finch;

class ErrorManager {
    public $collection = [];

    public function set( $file, $message, $level ) {
        if( ! isset($this->collection[$file])) {
            $this->collection[$file] =
            [
              "warning" => [],
              "error" => []
            ];
        }

        switch($level) {
            case "error": $this->errorsCount++; break;
            case "warning": $this->warningCount++; break;
        }

        array_push($this->collection[$file][$level], $message);
    }

    public function setError( $file, $message ) {
        return $this->set($file, $message, "error");
    }

    public function setWarning( $file, $message ) {
        return $this->set($file, $message, "warning");
    }

    public function getCollection() {
        return $this->__get('collection');
    }

    public function __get($var) {
        if(property_exists($this,$var)) {
            return $this->$var;
        } else {
            return NULL;
        }
    }


}
