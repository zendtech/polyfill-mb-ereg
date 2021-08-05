<?php

namespace ZendTechTest\Polyfill\MbEreg;

use PHPUnit\Framework\TestCase;
use ZendTech\Polyfill\MbEreg\MbEreg;

class MbEregiTest extends TestCase
{
    public function setUp(): void
    {
        if (! function_exists('mb_ereg')) {
            $this->markTestSkipped('mbstring extension required to verify polyfill');
        }
    }

    public function matchProvider(): array
    {
        return [
            'lowercase matches uppercase'                                         => ['z', 'XYZ'],
            'lowercase does not match uppercase when full pattern is not matched' => ['xyzp', 'XYZ'],
            'lowercase matches mb uppercase'                                      => ['ö', 'Öäü'],
        ];
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatching(string $pattern, string $string): void
    {
        $this->assertSame(
            (bool) mb_eregi($pattern, $string),
            (bool) MbEreg::match($pattern, $string, null, true)
        );
    }
}
