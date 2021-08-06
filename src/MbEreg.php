<?php

namespace ZendTech\Polyfill\MbEreg;

use InvalidArgumentException;

use function count;
use function get_class;
use function gettype;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function preg_last_error;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;

final class MbEreg
{
    /**
     * Known POSIX regex options
     *
     * @internal
     */
    const REGEX_OPTIONS = 'ixmsplne';

    /**
     * Map of POSIX regex options to PCRE flags
     *
     * @internal
     */
    const REGEX_OPTIONS_MAP = [
        'i' => 'i',
        'x' => 'x',
        'm' => 'm',
        's' => 's',
        'p' => 'ms',
        'l' => '',
        'n' => '',
        'e' => '',
    ];

    /**
     * Known POSIX regex modes
     *
     * @internal
     */
    const REGEX_MODES = 'jugcrzbd';

    /** @var string */
    private static $regexOptions = 'pr';

    /**
     * Polyfill mb_regex_encoding
     *
     * This is intentionally a no-op.
     */
    public static function regexEncoding()
    {
    }

    /**
     * @see https://php.net/mb_regex_set_options
     *
     * @param null|string $options
     * @return string
     */
    public static function regexSetOptions($options = null)
    {
        if ($options === null) {
            return self::$regexOptions;
        }

        $previousOptions = self::$regexOptions;
        $newOptions      = '';
        $mode            = '';

        $optionsLength = strlen($options);
        for ($i = 0; $i < $optionsLength; $i += 1) {
            $option = $options[$i];
            if (
                false !== strpos(self::REGEX_OPTIONS, $option)
                && false === strpos($newOptions, $option)
            ) {
                $newOptions .= $option;
                continue;
            }

            if (
                false !== strpos(self::REGEX_MODES, $option)
                && $mode !== $option
            ) {
                $mode = $option;
                continue;
            }
        }

        if ('' !== $mode) {
            $newOptions .= $mode;
        }

        if ('' !== $newOptions) {
            self::$regexOptions = $newOptions;
        }

        return $previousOptions;
    }

    /**
     * @param string $pattern Pattern to match
     * @param string $string String to match pattern against
     * @param null|string $options See https://php.net/mb_regex_set_options
     * @param bool $isCaseInsensitive
     * @param null|array $matches Reference
     * @return bool
     */
    public static function match(
        $pattern,
        $string,
        $options = null,
        $isCaseInsensitive = false,
        &$matches = null
    ) {
        $matches = [];
        $pattern = self::preparePatternWithFlags($pattern, $isCaseInsensitive, $options);
        $string  = self::prepareString($string, 2);
        $result  = (bool) preg_match($pattern, $string, $matches);

        if (preg_last_error()) {
            return false;
        }

        self::normalizeMatches($matches);

        return $result;
    }

    /**
     * @param string $pattern
     * @param string $replacement
     * @param string $string String in which to perform replacements
     * @param null|string $options See https://php.net/mb_regex_set_options
     * @param bool $isCaseInsensitive
     * @return string|false|null
     */
    public static function replace($pattern, $replacement, $string, $options = null, $isCaseInsensitive = false)
    {
        $pattern     = self::preparePatternWithFlags($pattern, $isCaseInsensitive, $options);
        $replacement = self::prepareReplacement($replacement);
        $string      = self::prepareString($string, 3);
        $result      = preg_replace($pattern, $replacement, $string);

        if (preg_last_error()) {
            return false;
        }

        return $result;
    }

    /**
     * @param string $pattern
     * @param string $string String in which to perform replacements
     * @param null|string $options See https://php.net/mb_regex_set_options
     * @return string|false|null
     */
    public static function replaceCallback($pattern, callable $callback, $string, $options = null)
    {
        $pattern = self::preparePatternWithFlags($pattern, false, $options);
        $string  = self::prepareString($string, 3);
        $result  = preg_replace_callback($pattern, $callback, $string);

        if (preg_last_error()) {
            return false;
        }

        return $result;
    }

    /**
     * @param string $pattern
     * @param bool $isCaseInsensitive
     * @param null|string $options
     * @return string
     */
    private static function preparePatternWithFlags($pattern, $isCaseInsensitive, $options)
    {
        $pattern = is_numeric($pattern) ? (string) $pattern : $pattern;

        if (! is_string($pattern)) {
            throw new InvalidArgumentException(sprintf(
                'Argument #1 ($pattern) must be of type string, %s given',
                is_object($pattern) ? get_class($pattern) : gettype($pattern)
            ));
        }

        if ($pattern === '') {
            throw new InvalidArgumentException('Argument #1 ($pattern) must not be empty');
        }

        $flags = self::preparePcreFlags($isCaseInsensitive, $options);
        return '/' . str_replace('/', '\\/', $pattern) . '/' . $flags;
    }

    /**
     * @param mixed $string
     * @param int $argumentPosition
     * @return string
     */
    private static function prepareString($string, $argumentPosition)
    {
        $string = is_numeric($string) ? (string) $string : $string;

        if (! is_string($string)) {
            throw new InvalidArgumentException(sprintf(
                'Argument #%d ($string) must be of type string, %s given',
                $argumentPosition,
                is_object($string) ? get_class($string) : gettype($string)
            ));
        }

        return $string;
    }

    /**
     * @param string $replacement
     * @return string
     */
    private static function prepareReplacement($replacement)
    {
        if (preg_match('/\\k\'[a-zA-Z0-9]+\'/', $replacement)) {
            throw new InvalidArgumentException(
                'Named backreferences in the form of "\k\'\'" are not supported by zendtech/polyfill-mb-ereg'
            );
        }

        if (preg_match('/\\k<[a-zA-Z0-9]+>/', $replacement)) {
            throw new InvalidArgumentException(
                'Named backreferences in the form of "\k<>" are not supported by zendtech/polyfill-mb-ereg'
            );
        }

        return preg_replace('/\\\\([1-9]\d*)/', '$$1', $replacement);
    }

    /**
     * @param bool $isCaseInsensitive
     * @param null|string $options
     * @return string
     */
    private static function preparePcreFlags($isCaseInsensitive, $options)
    {
        $flags   = $isCaseInsensitive ? 'ui' : 'u';
        $options = is_string($options) ? $options : self::$regexOptions;
        foreach (self::REGEX_OPTIONS_MAP as $option => $flag) {
            if (false === strpos($options, $option)) {
                continue;
            }

            $flags .= $flag;
        }
        return $flags;
    }

    /**
     * Normalized match captures
     *
     * - mb_ereg returns false for unmatched captures, instead of an empty string
     * - When unnamed captures are mixed with named captures, unnamed captures
     *   are not included in the list of matches.
     *
     * @return void
     */
    private static function normalizeMatches(array &$matches)
    {
        // mb_ereg returns false for unmatched captures, instead of empty string
        $indexedCaptures = [];
        $namedCaptures   = [];
        foreach ($matches as $key => $value) {
            if (0 === $key) {
                continue;
            }

            if (! is_int($key)) {
                $namedCaptures[] = $key;
                continue;
            }

            $indexedCaptures[] = $key;

            if ($value === '') {
                $matches[$key] = false;
                continue;
            }
        }

        if (
            count($indexedCaptures) !== count($namedCaptures)
            && count($namedCaptures) > 0
        ) {
            foreach ($indexedCaptures as $index) {
                unset($matches[$index]);
            }

            foreach ($namedCaptures as $index => $name) {
                $matches[$index + 1] = $matches[$name];
            }
        }
    }
}
