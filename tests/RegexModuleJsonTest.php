<?php
use Solleer\Router\RegexModuleJson;
class RegexModuleJsonTest extends PHPUnit_Framework_TestCase {
    private $json = <<<EOD
    {
        "conditions" : {
            "calendar" : ["/(\\\\d{4})/", "/(1[0-2]|\\\\d{1})/"],
            "event" : ["/(\\\\d+)/"]
        }
    }
EOD;

    private function getModuleJson($find = [], $json = null) {
        $moduleJson = $this->createMock('Config\Router\ModuleJson');

        $moduleJson->expects($this->once())
                 ->method('find')
                 ->with($this->equalTo($find));

        $moduleJson->method('getConfig')
                    ->willReturn(json_decode($json ?? $this->json));

        return $moduleJson;
    }

    public function testNormalModuleJson() {
        $moduleJson = $this->getModuleJson(['test']);

        $regexModule = new RegexModuleJson($moduleJson);

        $regexModule->find(['test']);
    }

    public function testOneConditionCapture() {
        $moduleJson = $this->getModuleJson(['events', 'event', '11']);

        $regexModule = new RegexModuleJson($moduleJson);

        $regexModule->find(['events', '11']);
    }

    public function testMultipleConditionsCapture() {
        $moduleJson = $this->getModuleJson(['events', 'calendar', '2017', '10']);

        $regexModule = new RegexModuleJson($moduleJson);

        $regexModule->find(['events', '2017', '10']);
    }

    public function testMultipleConditionsNonCapture() {
        $json = <<<EOD
        {
            "conditions" : {
                "calendar" : ["/\\\\d{4}/", "/(1[0-2]|\\\\d{1})/"],
                "event" : ["/(\\\\d+)/"]
            }
        }
EOD;
        $moduleJson = $this->getModuleJson(['events', 'calendar', '10'], $json);

        $regexModule = new RegexModuleJson($moduleJson);

        $regexModule->find(['events', '2017', '10']);
    }
}
