<?php

namespace Phase\Finch\Report;

use Phase\Finch\ErrorManager;
use Phase\Finch\Output;
use Phase\Finch\Console\Message;

class Terminal implements Output {

    protected $error;

    public function __construct(ErrorManager $error) {
        $this->error = $error;
    }

    public function printOut() {
        print "\n";

        foreach($this->error->getCollection() as $file => $errors) {
            $this->delimiter(strlen($file));
            Message::success($file);

            foreach($errors as $type => $error) {
                Message::$type("$type: " . count($errors[$type]));
                foreach($error as $err) {
                    Message::$type($err);
                }
            }
        }
    }

    public function delimiter( $length ) {
        for($i = 0; $i <= $length; $i++) {
            print "-";
        }

        print "\n";
    }
}
