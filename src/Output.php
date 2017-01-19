<?php

namespace Phase\Finch;

use Phase\Finch\ErrorManager;

interface Output {
    public function __construct(ErrorManager $error);
    public function printOut();
}
