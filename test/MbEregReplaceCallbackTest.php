<?php

declare(strict_types=1);

namespace ZendTechTest\Polyfill\MbEreg;

use PHPUnit\Framework\TestCase;
use ZendTech\Polyfill\MbEreg\MbEreg;

use function function_exists;
use function mb_ereg_replace_callback;
use function sprintf;
use function strlen;

class MbEregReplaceCallbackTest extends TestCase
{
    public function setUp(): void
    {
        if (! function_exists('mb_ereg')) {
            $this->markTestSkipped('mbstring extension required to verify polyfill');
        }
    }

    public function matchAndReplacementProvider(): array
    {
        $string = 'abc 123 #",; $foo';

        return [
            'indexed matches' => [
                'abc(3) 123(3) #",;(4) $foo(4)',
                '(\S+)',
                function (array $m): string {
                    return $m[1] . '(' . strlen($m[1]) . ')';
                },
                $string,
            ],
            'named matches'   => [
                '123-abc',
                '(?<word>\w+) (?<digit>\d+).*',
                function (array $m): string {
                    return sprintf('%s-%s', $m['digit'], $m['word']);
                },
                $string,
            ],
        ];
    }

    /**
     * @dataProvider matchAndReplacementProvider
     */
    public function testCallbackIsUsedForReplacement(
        string $expected,
        string $pattern,
        callable $replacement,
        string $original
    ): void {
        $extensionResult = mb_ereg_replace_callback($pattern, $replacement, $original);
        $this->assertSame(
            $expected,
            $extensionResult,
            'Extension result differed from expectation'
        );

        $this->assertSame(
            $extensionResult,
            MbEreg::replaceCallback($pattern, $replacement, $original),
            'Polyfill result differed from extension'
        );
    }
}
