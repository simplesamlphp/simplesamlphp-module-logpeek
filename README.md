![Build Status](https://github.com/simplesamlphp/simplesamlphp-module-logpeek/workflows/CI/badge.svg?branch=master)
[![Coverage Status](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-logpeek/branch/master/graph/badge.svg)](https://codecov.io/gh/simplesamlphp/simplesamlphp-module-logpeek)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-logpeek/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/simplesamlphp/simplesamlphp-module-logpeek/?branch=master)
[![Type Coverage](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-logpeek/coverage.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-logpeek)
[![Psalm Level](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-logpeek/level.svg)](https://shepherd.dev/github/simplesamlphp/simplesamlphp-module-logpeek)

logpeek module
==============

This module provides a web API that you can use to search for all to lines in the logs corresponding to a specific
session identifier.

Installation
------------

Once you have installed SimpleSAMLphp, installing this module is very simple. Just execute the following
command in the root of your SimpleSAMLphp installation:

```
composer.phar require simplesamlphp/simplesamlphp-module-logpeek:dev-master
```

where `dev-master` instructs Composer to install the `master` branch from the Git repository. See the
[releases](https://github.com/simplesamlphp/simplesamlphp-module-logpeek/releases) available if you
want to use a stable version of the module.

The module is disabled by default. If you want to enable the module once installed, you just need to add it to the `module.enable` array in `config.php`.
