<?php

declare(strict_types=1);

namespace ZendTechTest\Polyfill\MbEreg;

use PHPUnit\Framework\TestCase;
use ZendTech\Polyfill\MbEreg\MbEreg;

use function function_exists;

class MbEregiReplaceTest extends TestCase
{
    public function setUp(): void
    {
        if (! function_exists('mb_ereg')) {
            $this->markTestSkipped('mbstring extension required to verify polyfill');
        }
    }

    public function testReplacements(): void
    {
        $string       = 'Пеар';
        $replacements = [
            "й" => "i",
            "ц" => "c",
            "у" => "u",
            "к" => "k",
            "е" => "e",
            "н" => "n",
            "г" => "g",
            "ш" => "sh",
            "щ" => "sh",
            "з" => "z",
            "х" => "x",
            "ъ" => "\'",
            "ф" => "f",
            "ы" => "i",
            "в" => "v",
            "а" => "a",
            "п" => "p",
            "р" => "r",
            "о" => "o",
            "л" => "l",
            "д" => "d",
            "ж" => "zh",
            "э" => "ie",
            "ё" => "e",
            "я" => "ya",
            "ч" => "ch",
            "с" => "c",
            "м" => "m",
            "и" => "i",
            "т" => "t",
            "ь" => "\'",
            "б" => "b",
            "ю" => "yu",
            "Й" => "I",
            "Ц" => "C",
            "У" => "U",
            "К" => "K",
            "Е" => "E",
            "Н" => "N",
            "Г" => "G",
            "Ш" => "SH",
            "Щ" => "SH",
            "З" => "Z",
            "Х" => "X",
            "Ъ" => "\'",
            "Ф" => "F",
            "Ы" => "I",
            "В" => "V",
            "А" => "A",
            "П" => "P",
            "Р" => "R",
            "О" => "O",
            "Л" => "L",
            "Д" => "D",
            "Ж" => "ZH",
            "Э" => "IE",
            "Ё" => "E",
            "Я" => "YA",
            "Ч" => "CH",
            "С" => "C",
            "М" => "M",
            "И" => "I",
            "Т" => "T",
            "Ь" => "\'",
            "Б" => "B",
            "Ю" => "YU",
        ];

        foreach ($replacements as $pattern => $replacement) {
            $string = MbEreg::replace($pattern, $replacement, $string, null, true);
        }

        // This differs from the php-src test, which expects "Pear".
        // Logically, if we go through each replacment in order, the lowercase
        // version of the first letter will be encountered first, and thus it
        // will match that, and not the uppercase version.
        $this->assertSame('pear', $string);
    }
}
