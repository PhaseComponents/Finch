<?php

namespace Phase\Finch\Console;
/**
 * Common message colors that we use for console
 */
class Message {
    /**
     * Print error message
     * @param  string $msg
     * @return void
     */
    public static function error(string $msg) {
        print "\e[31m $msg \e[0m \n";
    }
    /**
     * Print warning message
     * @param  string $msg
     * @return void
     */
    public static function warning(string $msg) {
        print "\e[33m $msg \e[0m \n";
    }
    /**
     * Print success message
     * @param  string $msg
     * @return void
     */
    public static function success(string $msg) {
        print "\e[32m $msg \e[0m \n";
    }
    /**
     * Print info message
     * @param  string $msg
     * @return void
     */
    public static function info(string $msg) {
        print "\e[36m $msg \e[0m \n";
    }
}
