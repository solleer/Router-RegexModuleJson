<?php
use Solleer\Router\RegexModuleJson;
class RegexModuleJsonTest extends PHPUnit\Framework\TestCase {
    private $json = <<<EOD
    {
        "conditions" : {
            "calendar" : ["/(\\\\d{4})/", "/(1[0-2]|\\\\d{1})/"],
            "event" : ["/(\\\\d+)/"]
        }
    }
EOD;

    private function getRegexModuleJson($find = [], $json = null) {
        $moduleJson = $this->createMock('Level2\Router\Config\ModuleJson');

        $moduleJson->expects($this->once())
                 ->method('find')
                 ->with($this->equalTo($find));

        $moduleJson->method('getConfig')
                    ->willReturn(json_decode($json ?? $this->json, true));


        $authorize = $this->createMock('Solleer\Router\ModuleJsonAuthorize');
        $authorize->method('checkAuthorize')
            ->willReturn(true);

        return new RegexModuleJson($moduleJson, $authorize);
    }

    public function testNormalModuleJson() {
        $regexModule = $this->getRegexModuleJson(['test']);

        $regexModule->find(['test']);
    }

    public function testOneConditionCapture() {
        $regexModule = $this->getRegexModuleJson(['events', 'event', '11']);

        $regexModule->find(['events', '11']);
    }

    public function testMultipleConditionsCapture() {
        $regexModule = $this->getRegexModuleJson(['events', 'calendar', '2017', '10']);

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
        $regexModule = $this->getRegexModuleJson(['events', 'calendar', '10'], $json);

        $regexModule->find(['events', '2017', '10']);
    }
}
