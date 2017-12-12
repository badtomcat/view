<?php


use Badtomcat\Routing\RequestContext;
use Badtomcat\Routing\Exception\MethodNotAllowedException;

class ViewTest extends PHPUnit_Framework_TestCase
{

    public function testUsedef()
    {
    	$test = new \Badtomcat\View\Badtomcat(__DIR__.'/tpl/use-def.php');
    	$ret = $test->render();
        $this->assertEquals("<html><head></head><body>def</body></html>",trim($ret));
    }


    public function testSection()
    {
        $test = new \Badtomcat\View\Badtomcat(__DIR__.'/tpl/section.php');
        $ret = $test->render();
        $this->assertEquals("<html><head></head><body>balbalbal</body></html>",trim($ret));
    }

    public function testOverride()
    {
        $test = new \Badtomcat\View\Badtomcat(__DIR__.'/tpl/override.php');
        $ret = $test->render();
        $this->assertEquals("<html><head></head><body>defbalbalbal</body></html>",trim($ret));
    }


    public function testMutiSection()
    {
        $test = new \Badtomcat\View\Badtomcat(__DIR__.'/tpl/muti-section.php');
        $ret = $test->render();
        $this->assertTrue(1 === preg_match("/^\s*<body>sec1\-sec2<\/body>\s*abc\s*$/",$ret));
    }

    public function testNestSection()
    {
        $test = new \Badtomcat\View\Badtomcat(__DIR__.'/tpl/nest-section.php');
        $ret = $test->render();
        $this->assertTrue(1 === preg_match("/^\s*<html><head><\/head><body>balbalbal\s*<\/body><\/html>\s*foo\s*$/",$ret));
    }
}

