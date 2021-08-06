<?php

use ZendTech\Polyfill\MbEreg as p;

if (! function_exists('mb_ereg')) {
    /**
     * @see https://php.net/mb_ereg
     *
     * @param string $pattern
     * @param string $string
     * @param null|array $matches
     * @return bool
     */
    function mb_ereg($pattern, $string, &$matches = null)
    {
        return p\MbEreg::match($pattern, $string, null, false, $matches);
    }
}

if (! function_exists('mb_eregi')) {
    /**
     * @see https://php.net/mb_eregi
     *
     * @param string $pattern
     * @param string $string
     * @param null|array $matches
     * @return bool
     */
    function mb_eregi($pattern, $string, &$matches = null)
    {
        return p\MbEreg::match($pattern, $string, null, true, $matches);
    }
}

if (! function_exists('mb_ereg_match')) {
    /**
     * @see https://php.net/mb_ereg_match
     *
     * @param string $pattern
     * @param string $string
     * @param null|string $options See https://php.net/mb_regex_set_options
     * @return bool
     */
    function mb_ereg_match($pattern, $string, $options = null)
    {
        return p\MbEreg::match($pattern, $string, $options);
    }
}

if (! function_exists('mb_ereg_replace')) {
    /**
     * @see https://php.net/mb_ereg_replace
     *
     * @param string $pattern
     * @param string $replacement
     * @param string $string String in which to perform replacements
     * @param null|string $options See https://php.net/mb_regex_set_options
     * @return string|false|null
     */
    function mb_ereg_replace($pattern, $replacement, $string, $options = null)
    {
        return p\MbEreg::replace($pattern, $replacement, $string, $options);
    }
}

if (! function_exists('mb_eregi_replace')) {
    /**
     * @see https://php.net/mb_eregi_replace
     *
     * @param string $pattern
     * @param string $replacement
     * @param string $string String in which to perform replacements
     * @param null|string $options See https://php.net/mb_regex_set_options
     * @return string|false|null
     */
    function mb_eregi_replace($pattern, $replacement, $string, $options = null)
    {
        return p\MbEreg::replace($pattern, $replacement, $string, $options, true);
    }
}

if (! function_exists('mb_ereg_replace_callback')) {
    /**
     * @param string $pattern
     * @param string $string String in which to perform replacements
     * @param null|string $options See https://php.net/mb_regex_set_options
     * @return string|false|null
     */
    function mb_ereg_replace_callback($pattern, callable $callback, $string, $options = null)
    {
        return p\MbEreg::replaceCallback($pattern, $callback, $string, $options);
    }
}

if (! function_exists('mb_regex_encoding')) {
    /**
     * @return void
     */
    function mb_regex_encoding()
    {
        p\MbEreg::regexEncoding();
    }
}

if (! function_exists('mb_regex_set_options')) {
    /**
     * @see https://www.php.net/mb_regex_set_options
     *
     * @param null|string $options Options to set
     * @return string Previous options
     */
    function mb_regex_set_options($options)
    {
        return p\MbEreg::regexSetOptions();
    }
}
