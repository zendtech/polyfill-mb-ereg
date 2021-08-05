<?php

namespace ZendTechTest\Polyfill\MbEreg;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZendTech\Polyfill\MbEreg\MbEreg;

class MbEregReplaceTest extends TestCase
{
    public function setUp(): void
    {
        if (! function_exists('mb_ereg')) {
            $this->markTestSkipped('mbstring extension required to verify polyfill');
        }
    }

    public function replacementProvider(): array
    {
        $stringAscii  = 'abc def';
        $stringMb     = '日本語テキストです。01234５６７８９。';

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            // mb_ereg_replace tests
            'whitespace pattern matching string'                        => ['a-b-c-d-e', ' ', '-', 'a b c d e'],
            'subpattern replacement'                                    => ['[abc] [def] [ghi]', '([a-z]+)', '[\\1]', 'abc def ghi'],
            // mb_ereg_replace_basic tests
            'ascii string, pattern, and replacement with placeholder'   => ['abc  123', '(.*)def', '\\1 123', $stringAscii],
            'ascii string, pattern, and replacement w/o placeholder'    => ['abc def', '123', 'abc', $stringAscii],
            'mb string, ascii pattern, replacement with placeholder'    => ['日本語_____1234５６７８９。', '(日本語).*?([1-9]+)', '\\1_____\\2', $stringMb],
            'mb string, ascii pattern, replacement w/o placeholder'     => ['日本語テキストです。01234５６７８９。', '世界', '_____', $stringMb],
            // mb_ereg_replace_named_subpatterns tests
            'Empty backref is ignored'                                  => ['-\k<>-', '(\w)\1', '-\k<>-', 'AA'],
            // compat-01
            'compat test 01'                                            => ['abcdef', '123', 'def', 'abc123'],
            // compat-02
            'compat test 02'                                            => ['abc', '123', '', 'abc123'],
            // compat-03
            'compat test 03'                                            => ['\'test', "\\\\'", "'", "\\'test"],
            // compat-04
            'compat test 04'                                            => ['That is a nice and simple string', '^This', 'That', 'This is a nice and simple string'],
            // compat-05
            'compat test 05'                                            => ['', 'abcd', '', 'abcd'],
            // compat-06
            'compat test 06'                                            => ["123 abc +-|=\n", '([a-z]*)([-=+|]*)([0-9]+)', "\\3 \\1 \\2\n", 'abc+-|=123'],
            // compat-07
            'compat test 07'                                            => ['abc2222222222def2222222222', '1(2*)3', '\\1def\\1', 'abc122222222223'],
            // compat-08
            'compat test 08'                                            => ['abcdef123ghi', '123', 'def\\0ghi', 'abc123'],
            // compat-09
            // SKIPPED
            // PCRE interpets \1 as the first captured group, and since there
            // are none, no value is interpolated in the replacement. mbstring
            // keeps it as a literal when there are no captured groups. This is
            // likely a mistake.
            // 'compat test 09'                                            => ['abcdef\1ghi', '123', 'def\1ghi', 'abc123'],
            // compat-10
            // SKIPPED
            // PCRE and mbstring differ in how they interpret repeated escape
            // sequences; this one cannot be massaged to work correctly.
            // 'compat test 10'                                            => ['abcdef\g\\hi\\', '123', 'def\\g\\\\hi\\', 'abc123'],
            // compat-11
            'compat test 11'                                            => ['\2', 'a(.*)b(.*)c', '\\1', 'a\\2bxc'],
            // compat-12
            'compat test 12'                                            => ['zabc123', '^', 'z', 'abc123'],
            // compat-13
            'compat test 13'                                            => ['abc123abc', '\?', 'abc', '?123?'],
        ];
        // phpcs:enable
    }

    /**
     * @dataProvider replacementProvider
     */
    public function testReplacement(
        string $expected,
        string $pattern,
        string $replacement,
        string $original
    ): void {
        $extensionResult = mb_ereg_replace($pattern, $replacement, $original);

        $this->assertSame($expected, $extensionResult, 'Extension result does not match expectations');

        $polyfillResult = MbEreg::replace($pattern, $replacement, $original);

        $this->assertSame(
            $extensionResult,
            $polyfillResult,
            sprintf('Polyfill result "%s" does not match extension result "%s"', $polyfillResult, $extensionResult)
        );
    }

    public function unsupportedReplacementProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            '\k<word> syntax'                                           => ['(?<a>\s*)(?<b>\w+)(?<c>\s*)', '\k<a>_\k<b>_\k<c>', 'a b c d e'],
            '\k\'word\' syntax'                                         => ['(?<word>[a-z]+)',"<\k'word'>", 'abc def ghi'],
            'numbered captures with \k<n> syntax'                       => ['(1)(2)(3)(4)(5)(6)(7)(8)(9)(a)(\10)', '\k<0>-\k<10>-', '123456789aa'],
            'numbered captures with \k\'n\' syntax'                     => ['(1)(2)(3)(4)(5)(6)(7)(8)(9)(a)(\10)', "\k'0'-\k'10'-", '123456789aa'],
            'backref 0 works but backref 1 is ignored'                  => ['a', "\k'0'_\k<01>", 'a'],
        ];
        // phpcs:enable
    }

    /**
     * @dataProvider unsupportedReplacementProvider
     */
    public function testRaisesExceptionForUnsupportedReplacements(string $pattern, string $replacement, string $original): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Named backreferences');
        MbEreg::replace($pattern, $replacement, $original);
    }

    public function testUnclosedBackrefIsIgnored(): void
    {
        $this->assertSame(
            '-\k<a',
            MbEreg::replace('(?<a>\w+)', '-\k<a', 'AA')
        );
    }

    public function testNumberedBackrefInReplacementIsIgnoredIfNamedBackrefIsPresentInPattern(): void
    {
        $this->markTestSkipped(
            'This behavior differs in the polyfill since the PCRE engine does not support named backrefs in replacements'
        );

        $this->assertSame(
            '-\1-',
            MbEreg::replace('(?<a>A)\k<a>', '-\1-', 'AA')
        );
    }

    public function invalidPatternProvider(): array
    {
        $replacement   = 'string_val';
        $original      = 'string_val';
        $option        = '';
        $classInstance = new class () {
            public function __toString()
            {
                return 'UTF-8';
            }
        };

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'bool - true'             => [true, $replacement, $original, $option],
            'bool - false'            => [false, $replacement, $original, $option],
            'string - empty'          => ['', $replacement, $original, $option],
            'object - stringable'     => [$classInstance, $replacement, $original, $option],
        ];
        // phpcs:enable
    }

    /**
     * @dataProvider invalidPatternProvider
     * @param mixed $pattern
     */
    public function testInvalidPatternsResultInExceptions(
        $pattern,
        string $replacement,
        string $original,
        string $option
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument #1');
        MbEreg::replace($pattern, $replacement, $original, $option);
    }

    public function unusualPatternProvider(): array
    {
        $replacement   = 'string_val';
        $original      = 'string_val';
        $option        = '';
        $heredoc       = <<<END
UTF-8
END;

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'integer - zero'          => [$replacement, 0, $replacement, $original, $option],
            'integer - one'           => [$replacement, 1, $replacement, $original, $option],
            'integer - multidigit'    => [$replacement, 12345, $replacement, $original, $option],
            // Skipping this one, as I get errors from the extension
            // 'integer - negative'      => [$replacement, -2345, $replacement, $original, $option],
            'float'                   => [$replacement, 10.5, $replacement, $original, $option],
            // Skipping this one, as I get errors from the extension
            // 'float - negative'        => [$replacement, -10.5, $replacement, $original, $option],
            'float - exponential'     => [$replacement, 12.3456789000e10, $replacement, $original, $option],
            'float - neg exponential' => [$replacement, 12.3456789000E-10, $replacement, $original, $option],
            // This next differs from php-src; the extension actually behaves
            // differently than php-src indicates.
            'float - fractional'      => [$replacement, .5, $replacement, $original, $option],
            'string - encoding'       => [$replacement, 'UTF-8', $replacement, $original, $option],
            'string - heredoc'        => [$replacement, $heredoc, $replacement, $original, $option],
        ];
        // phpcs:enable
    }

    /**
     * @dataProvider unusualPatternProvider
     * @param mixed $pattern
     */
    public function testUnusualPatternsResultInExpectedOutput(
        string $expected,
        $pattern,
        string $replacement,
        string $original,
        string $option
    ): void {
        $extensionResult = mb_ereg_replace($pattern, $replacement, $original, $option);
        $this->assertSame($expected, $extensionResult, 'Extension result differs from expected');
        $this->assertSame(
            $extensionResult,
            MbEreg::replace($pattern, $replacement, $original, $option),
            'Polyfill result differs from extension'
        );
    }
}
