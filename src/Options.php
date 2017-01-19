<?php

namespace Phase\Finch;

use Phase\Finch\Console\Message;

/**
 * Options class holds methods that corresponds
 * to names of passed arguments from console
 */
class Options {
    /**
     * Array that holds list of all arguments passed from console
     * @var array $options
     */
    protected $options;
    /**
     * Display usage of script
     * @return void
     */
    protected function usage() {
        $usage = memory_get_peak_usage(true)/1024/1024;

        $dat = getrusage();
        $dat["ru_utime.tv_usec"] = ($dat["ru_utime.tv_sec"]*1e6 + $dat["ru_utime.tv_usec"]) - Finch_RUSAGE;
        $time = (microtime(true) - Finch_TUSAGE) * 1000000;

        $cpu = sprintf("%01.2f", ($dat["ru_utime.tv_usec"] / $time) * 100);

        Message::info("Analyze finished, {$usage}MB memory used. Finished in {$cpu} miliseconds");
    }

}
