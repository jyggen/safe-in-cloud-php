# SafeInCloud API Client and CLI Tool

[![Latest Version](https://img.shields.io/github/release/jyggen/safe-in-cloud-php.svg?style=flat-square)](https://github.com/jyggen/safe-in-cloud-php/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/jyggen/safe-in-cloud-php/master.svg?style=flat-square)](https://travis-ci.org/jyggen/safe-in-cloud-php)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/jyggen/safe-in-cloud-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/jyggen/safe-in-cloud-php/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/jyggen/safe-in-cloud-php.svg?style=flat-square)](https://scrutinizer-ci.com/g/jyggen/safe-in-cloud-php)
[![Total Downloads](https://img.shields.io/packagist/dt/jyggen/safe-in-cloud-php.svg?style=flat-square)](https://packagist.org/packages/jyggen/safe-in-cloud-php)

An API client and CLI tool to work and communicate with the HTTP API built into the SafeInCloud software. This package is compliant with [PSR-1], [PSR-2] and [PSR-4].

- [Find on Packagist/Composer](https://packagist.org/packages/jyggen/safe-in-cloud-php)

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

## Usage

First you need to run the command `authenticate` to get an authentication token from your SafeInCloud client.

```bash
./bin/safeincloud authenticate
Enter your password:
lWg/CBmrcEs6XiAgXl33qg==
```

```bash
./bin/safeincloud accounts --token lWg/CBmrcEs6XiAgXl33qg== example.com
[{"title":"example.com","login":"foo","password":"bar"}]
```

```bash
./bin/safeincloud logins --token lWg/CBmrcEs6XiAgXl33qg==
["foo","bar","baz","qux"]
```

## License

The MIT License (MIT). Please see [License File](https://github.com/pwnraid/bnet/blob/master/LICENSE) for more information.

This software is not endorsed, sponsored, affiliated with or otherwise authorized by SafeInCloud.
