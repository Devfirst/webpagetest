<?php

require_once __DIR__ . '/../include/TestInfo.php';
require_once __DIR__ . '/../include/TestStepResult.php';
require_once __DIR__ . '/../include/TestRunResults.php';
require_once __DIR__ . '/../include/XmlResultGenerator.php';

class XmlResultGeneratorTest extends PHPUnit_Framework_TestCase {
  /* @var TestInfo */
  private $testInfo;
  private $xmlInfoDomainBreakdown;

  public function setUp() {
    $rawTestInfo = array();
    $this->testInfo = TestInfo::fromValues("160628_AB_C", "/test/path", $rawTestInfo);
    $this->xmlInfoDomainBreakdown = array(XmlResultGenerator::INFO_DOMAIN_BREAKDOWN);
    ob_start();
  }

  public function tearDown() {
    ob_end_clean();
  }

  public function testPrintMedianRunSinglestep() {
    $run = $this->getTestRunResults(1);
    $xmlGenerator = new XmlResultGenerator($this->testInfo, "", new FileHandler(), $this->xmlInfoDomainBreakdown, true);
    $xmlGenerator->printMedianRun($run);
    $xml = simplexml_load_string(ob_get_contents());
    $this->assertEquals("2", $xml->run);
    $this->assertEquals("300", $xml->TTFB);
    $this->assertEquals("6000", $xml->loadTime);
    $this->assertTrue(isset($xml->foo));
    $this->assertEquals("lorem", $xml->foo);
    $this->assertTrue(isset($xml->domains));
  }

  public function testPrintMedianRunSinglestepForceMultistep() {
    $run = $this->getTestRunResults(1);
    $xmlGenerator = new XmlResultGenerator($this->testInfo, "", new FileHandler(), $this->xmlInfoDomainBreakdown, true);
    $xmlGenerator->forceMultistepFormat(true);
    $xmlGenerator->printMedianRun($run);
    $xml = simplexml_load_string(ob_get_contents());
    $this->assertEquals("2", $xml->run);
    $this->assertEquals("300", $xml->TTFB);
    $this->assertEquals("6000", $xml->loadTime);
    $this->assertFalse(isset($xml->foo));
    $this->assertFalse(isset($xml->domains));
  }

  public function testPrintMedianRunMultistep() {
    $run = $this->getTestRunResults(3);
    $xmlGenerator = new XmlResultGenerator($this->testInfo, "", new FileHandler(), $this->xmlInfoDomainBreakdown, true);
    $xmlGenerator->printMedianRun($run);
    $xml = simplexml_load_string(ob_get_contents());
    $this->assertEquals("2", $xml->run);
    $this->assertEquals("900", $xml->TTFB);
    $this->assertEquals("9000", $xml->loadTime);
    $this->assertFalse(isset($xml->foo));
    $this->assertFalse(isset($xml->domains));
  }

  public function testPrintRunSinglestep() {
    $run = $this->getTestRunResults(1);
    $xmlGenerator = new XmlResultGenerator($this->testInfo, "", new FileHandler(), $this->xmlInfoDomainBreakdown, true);
    $xmlGenerator->printRun($run);
    $xml = simplexml_load_string(ob_get_contents());

    $this->assertEquals("300", $xml->results->TTFB);
    $this->assertEquals("6000", $xml->results->loadTime);
    $this->assertTrue(isset($xml->results->foo));
    $this->assertEquals("lorem", $xml->results->foo);
    $this->assertTrue(isset($xml->domains));
    $this->assertEquals("/result/160628_AB_C/2/screen_shot/cached/", $xml->pages->screenShot);
    $this->assertEquals("1", $xml->numSteps);
  }

  private function getTestStepArray() {
    $step1 = array('result' => 0, 'TTFB' => 300, 'loadTime' => 6000, 'foo' => 'lorem');
    $step2 = array('result' => 0, 'TTFB' => 100, 'loadTime' => 2000, 'foo' => 'ipsum');
    $step3 = array('result' => 99999, 'TTFB' => 500, 'loadTime' => 1000, 'foo' => 'dolor');

    $stepResults = array(
      1 => TestStepResult::fromPageData($this->testInfo, $step1, 2, true, 1),
      2 => TestStepResult::fromPageData($this->testInfo, $step2, 2, true, 2),
      3 => TestStepResult::fromPageData($this->testInfo, $step3, 2, true, 3)
    );
    return $stepResults;
  }

  private function getTestRunResults($numSteps) {
    $steps = $this->getTestStepArray();
    return TestRunResults::fromStepResults($this->testInfo, 2, true, array_slice($steps, 0, $numSteps));
  }
}
