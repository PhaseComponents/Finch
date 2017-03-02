<?php

namespace Phase\Finch\Console;

use LucidFrame\Console\ConsoleTable;

abstract class Table extends ConsoleTable {
    public function draw() {
        foreach($this->error->getCollection() as $file => $errors) {
            $this->setHeaders(array('File', 'Notice', 'Message'));
            foreach($errors as $notice => $messages) {
                foreach($messages as $message) {
                $this->addRow()
                    ->addColumn($file)
                    ->addColumn($notice)
                    ->addColumn($message);
                }
            }
        }

        $this->display();
    }
}
