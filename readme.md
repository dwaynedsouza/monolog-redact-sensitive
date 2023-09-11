# Monolog data redaction
[![Build Status](https://travis-ci.org/andybeak/monolog-redact-sensitive.svg?branch=master)](https://travis-ci.org/andybeak/monolog-redact-sensitive)
[![Maintainability](https://api.codeclimate.com/v1/badges/c6df2bff64c356f48bcd/maintainability)](https://codeclimate.com/github/andybeak/monolog-redact-sensitive/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/c6df2bff64c356f48bcd/test_coverage)](https://codeclimate.com/github/andybeak/monolog-redact-sensitive/test_coverage)

These [Monolog](https://github.com/Seldaek/monolog/blob/master/README.md) processors will identify and strip out emails and credit cards respectively.

WARNING: These processors will json serialise your $context. This may cause some undesired side-effects.

## Installation

Install the latest version with

    composer require muhammadgant/monolog-redact-sensitive

## Usage

Here is example usage:

    <?php
    require('vendor/autoload.php');
    use MuhammadGant\Monolog\Redact\Processor\RedactCreditCardProcessor;
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    $generalLogger = new Logger('general');
    $streamHandler = new StreamHandler(__DIR__ . DIRECTORY_SEPARATOR . 'example.log', Logger::DEBUG);
    $generalLogger->pushHandler($streamHandler);

    $processor = new RedactCreditCardProcessor();
    $generalLogger->pushProcessor($processor);

    $generalLogger->debug('Visa test: 4111111111111111 is a test card number');

The credit card number will be removed in the file

## Credit card stripper

The code looks for potential credit cards by using regex to identify strings of numbers that are the right length.  These numbers may contain spaces or dashes between them.

The regex is not perfect

The numbers are validated and if they are valid credit card numbers they are redacted.

## Email stripper

Identifying email addresses with regex is tricky.

By default the processor uses a good regex pattern, but you might find that it is too
expensive to use.

You can call the `setRegex` function to replace the regex with something less complicated if you find your performance slipping.

The signature of this function is:

    public function setRegex(string $regex): RedactEmailProcessor