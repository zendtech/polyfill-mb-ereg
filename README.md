# zendtech/polyfill-mb-ereg - mb_ereg polyfill

## Installation

```bash
composer require zendtech/polyfill-mb-ereg
```

## Usage

This package provides polyfills for the following functions

- mb_ereg
- mb_eregi
- mb_ereg_match
- mb_ereg_replace
- mb_ereg_replace_callback
- mb_eregi_replace
- mb_regex_encoding
- mb_regex_set_options

It can safely be used alongside [symfony/polyfill-mbstring](https://symfony.com/components/Polyfill%20Mbstring), as that polyfill does not provide polyfills for the ereg functions.

The main reason to use this package is if:

- You are using PHP on Windows
- You are using PHP 7.4+ on that platform
- And libraries you use use mb_ereg functionality

Starting with PHP 7.4, php.net stopped shipping the oniguruma library on which the mbstring extension is based, in favor of using system packages.
Microsoft started building its PHP packages against libmbfl instead, as it is a roughly equivalent library for Windows.
Unfortunately, that library does not expose the ereg functionality, which means that any PHP binaries built against it do not have this functionality present.

In most cases, switching to PCRE functionality is preferred, as it can match many multibyte sequences out-of-the-box, particularly when using the `u` PCRE flag with your pattern.
However, when using third party packages, you may not be able to control whether or not PCRE is used — and hence the reason for this package.

## Caveats

Please note the following caveats and behavior differences for this polyfill.

- This package DOES NOT provide polyfills for any of the `mb_ereg_search` family of functions, as these are not often used, and rely on global state and pointers that are brittle even under the native extension.

- The polyfill for `mb_ereg()`:
  - Always returns a boolean.
    In PHP versions prior to PHP 8, the native extension would return the number of bytes matched, or, if the length of the match was zero, the integer 1.
    Since the typical use case for the function is for boolean testing, the polyfill returns a boolean always.

  - Always sets `$matches`, if passed, to an array, even if no matches were made.
    This has been the behavior since PHP 7.1, and it did not make sense to vary the behavior for PHP 5.6.

- The polyfill DOES NOT support named backrefs of the form `\k''` or `\k<>` in replacements provided to `mb_ereg_replace()`, `mb_eregi_replace()`, or `mb_ereg_replace_callback()`.
  This is due to the fact that the PCRE engine included in PHP does not support them.
