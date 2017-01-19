<?php

use Phase\CodeStyle\File\FileCollection;
use Phase\CodeStyle\Init;
use Phase\CodeStyle\Config;
use Phase\CodeStyle\Analyzer;
/**
 * Initialize CodeStyle
 */
class InitTest extends PHPUnit_Framework_TestCase {
    protected $config;

    public function test_construct() {
        $this->config = new Config();

        $dat = getrusage();
        define('Finch_TUSAGE', microtime(true));
        define('Finch_RUSAGE', $dat["ru_utime.tv_sec"]*1e6+$dat["ru_utime.tv_usec"]);

        $options = [
         "path" => "./tests/AnalyzeFiles",
         "rules" => "./tests/testRules"
        ];

        // init rules to force
        $path = isset($options["path"]) ? $options["path"] : ".";
        $collection = new FileCollection($path);

        // initialze analyzer and run it
        $rules = isset($options["rules"]) ? $options["rules"] : null;
        $analyzer = new Analyzer($collection, $this->config->load($rules), $options);
        $analyzer->run($dat);

        $this->assertEquals(count($analyzer->__get('error')->getCollection()), 15);

        $this->assertArrayHasKey("./tests/AnalyzeFiles/Linelength_Exceeded.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/Namespace_Missing.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/Else_used.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/Elseif_used.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/InlineBracket_used.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/Goto_used.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/Eval_used.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/VarByRef.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/ReturnByRef.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/Globals.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/IndentSpace.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/UseClassBubbleSort.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/PhpFileClosingTag.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/StudlyCapsClassNameNotUsed.php", $analyzer->__get('error')->getCollection());
        $this->assertArrayHasKey("./tests/AnalyzeFiles/CamelCaseMethodsNotUsed.php", $analyzer->__get('error')->getCollection());

        $this->assertEquals(count($analyzer->__get('error')->getCollection()["./tests/AnalyzeFiles/Else_used.php"]["error"]), 2);
        $this->assertEquals(count($analyzer->__get('error')->getCollection()["./tests/AnalyzeFiles/CamelCaseMethodsNotUsed.php"]["error"]), 2);
    }
}
