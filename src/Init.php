<?php

namespace Phase\Finch;

use Phase\Finch\File\FileCollection;

/**
 * Initialize Finch
 */
class Init extends Config {
    /**
     * Construct instance to start Analyze
     *
     * @param array $argv Arguments passed from terminal
     * @return void
     */
    public function __construct(array $argv){
        $dat = getrusage();
        define('Finch_TUSAGE', microtime(true));
        define('Finch_RUSAGE', $dat["ru_utime.tv_sec"]*1e6+$dat["ru_utime.tv_usec"]);

        $options = getopt("vh:",["rules::",
                               "path::",
                               "usage",
                               "walk",
                               "help"
                             ]);


         if(isset($options["help"]) || isset($options["h"])) {
             print file_get_contents("help.txt");
             return;
         }

        // init rules to force
        $path = isset($options["path"]) ? $options["path"] : ".";
        $collection = new FileCollection($path);

        // initialze analyzer and run it
        $rules = isset($options["rules"]) ? $options["rules"] : null;
        $analyzer = new Analyzer($collection, $this->load($rules), $options);
        $analyzer->run($dat);
    }
}
