# CLI

This library is to simplify cli development in php.

## Usage

```php
use UnknownRori\Console\Console;

$app = new Console();

// Add command
$app->addCommand('sum', "It sum a two number", function (float $a, float $b) {
    return $a + $b;
});

// Serving the cli
echo $app->serve($argv);
```

## Getting Started

Installing via composer

`> composer require unknownrori/cli`

### Requirement

```
php 7.4
```
