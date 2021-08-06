<?php

declare(strict_types=1);

namespace ZendTechTest\Polyfill\MbEreg;

use PHPUnit\Framework\TestCase;
use Throwable;
use ZendTech\Polyfill\MbEreg\MbEreg;

use function base64_decode;
use function function_exists;
use function mb_ereg;
use function sprintf;

class MbEregTest extends TestCase
{
    public function setUp(): void
    {
        if (! function_exists('mb_ereg')) {
            $this->markTestSkipped('mbstring extension required to verify polyfill');
        }
    }

    public function invalidArgumentProvider(): array
    {
        return [
            'null pattern'  => ['Argument #1 ($pattern) must be of type string, NULL given', null, 2],
            'array pattern' => ['Argument #1 ($pattern) must be of type string, array given', [], 2],
            'empty pattern' => ['Argument #1 ($pattern) must not be empty', '', 2],
            'null string'   => ['Argument #2 ($string) must be of type string, NULL given', '2', null],
            'array string'  => ['Argument #2 ($string) must be of type string, array given', '2', []],
        ];
    }

    /**
     * @dataProvider invalidArgumentProvider
     * @param mixed $pattern
     * @param mixed $string
     */
    public function testInvalidInput(string $expectedMessage, $pattern, $string): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($expectedMessage);
        MbEreg::match($pattern, $string);
    }

    public function matchProvider(): array
    {
        $stringAscii   = 'This is an English string. 0123456789.';
        $regexAscii1   = '(.*is)+.*\.[[:blank:]][0-9]{9}';
        $regexAscii2   = '.*is+';
        $matchesAscii1 = [
            base64_decode('VGhpcyBpcyBhbiBFbmdsaXNoIHN0cmluZy4gMDEyMzQ1Njc4'),
            base64_decode('VGhpcyBpcyBhbiBFbmdsaXM='),
        ];
        $matchesAscii2 = [
            base64_decode('VGhpcyBpcyBhbiBFbmdsaXM='),
        ];

        $stringMb   = base64_decode('5pel5pys6Kqe44OG44Kt44K544OI44Gn44GZ44CCMDEyMzTvvJXvvJbvvJfvvJjvvJnjgII=');
        $regexMb1   = base64_decode('KOaXpeacrOiqnikuKj8oWzEtOV0rKQ==');
        $regexMb2   = base64_decode('5LiW55WM');
        $matchesMb1 = [
            base64_decode('5pel5pys6Kqe44OG44Kt44K544OI44Gn44GZ44CCMDEyMzQ='),
            base64_decode('5pel5pys6Kqe'),
            base64_decode('MTIzNA=='),
        ];
        $matchesMb2 = [];

        $stringMbCapture1  = '  中国';
        $stringMbCapture2  = '国';
        $regexMbCapture1   = '(?<wsp>\s*)(?<word>\w+)';
        $regexMbCapture2   = '(\s*)(?<word>\w+)';
        $matchesMbCapture1 = [
            0      => '  中国',
            1      => '  ',
            2      => '中国',
            'wsp'  => '  ',
            'word' => '中国',
        ];
        $matchesMbCapture2 = [
            0      => '国',
            1      => false,
            2      => '国',
            'wsp'  => false,
            'word' => '国',
        ];
        $matchesMbCapture3 = [
            0      => '  中国',
            1      => '中国',
            'word' => '中国',
        ];

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'integers'                                        => [1, 2, 3, []],
            'integer pattern, empty string'                   => [1, '', [], []],
            'negative integers'                               => [-1, -1, -1, ['-1']],
            'ascii with captures and char classes'            => [$regexAscii1, $stringAscii],
            'ascii without captures or char classes'          => [$regexAscii2, $stringAscii],
            'ascii with captures and char classes, matches'   => [$regexAscii1, $stringAscii, [], $matchesAscii1],
            'ascii without captures or char classes, matches' => [$regexAscii2, $stringAscii, [], $matchesAscii2],
            'mb with captures and char classes'               => [$regexMb1, $stringMb],
            'mb without captures or char classes'             => [$regexMb2, $stringMb],
            'mb with captures and char classes, matches'      => [$regexMb1, $stringMb, [], $matchesMb1],
            'mb without captures or char classes, matches'    => [$regexMb2, $stringMb, [], $matchesMb2],
            'mb with multiple matching named captures'        => [$regexMbCapture1, $stringMbCapture1, [], $matchesMbCapture1],
            'mb matching one of two named captures'           => [$regexMbCapture1, $stringMbCapture2, [], $matchesMbCapture2],
            'mb matching named and unnamed captures'          => [$regexMbCapture2, $stringMbCapture1, [], $matchesMbCapture3],
        ];
        // phpcs:enable
    }

    /**
     * @dataProvider matchProvider
     * @param scalar $pattern
     * @param scalar $string
     * @param mixed $matches
     */
    public function testMatchResults($pattern, $string, $matches = null, ?array $expectedMatches = null)
    {
        if ($matches === null) {
            $this->assertSame(
                (bool) mb_ereg($pattern, $string),
                MbEreg::match($pattern, $string),
                sprintf(
                    'MbEreg::match(\'%1$s\', \'%2$s\') did not match results of mb_ereg(\'%1$s\', \'%2$s\')',
                    $pattern,
                    $string
                )
            );
            return;
        }

        $nativeMatches   = $matches;
        $polyfillMatches = $matches;

        $this->assertSame(
            (bool) mb_ereg($pattern, $string, $nativeMatches),
            MbEreg::match($pattern, $string, null, false, $polyfillMatches),
            sprintf(
                'MbEreg::match(\'%1$s\', \'%2$s\') did not match results of mb_ereg(\'%1$s\', \'%2$s\')',
                $pattern,
                $string
            )
        );

        $this->assertEquals($expectedMatches, $nativeMatches, 'Native matches did not match expectations');
        $this->assertEquals($nativeMatches, $polyfillMatches, 'Polyfill matches did not match native matches');
    }
}
