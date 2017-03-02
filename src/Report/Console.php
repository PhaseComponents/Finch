<?php

namespace Phase\Finch\Report;

use Phase\Finch\ErrorManager;
use Phase\Finch\Console\Table;
use Phase\Finch\Console\Message;

class Console extends Table {

    protected $error;

    public function __construct(ErrorManager $error) {
        $this->error = $error;
    }

    public function write() {
        if(count($this->error->getCollection()) < 1) {
            Message::success("Everything is OK!");
            return 0;
        }

        $this->draw();
    }
}
