# Monolog data redaction

These [Monolog](https://github.com/Seldaek/monolog/blob/master/README.md) processors will identify and strip out emails and credit cards respectively.

WARNING: These processors will json serialise your $context. This may cause some undesired side-effects.

## Installation

Install the latest version with

    composer require andybeak/monolog-redact-sensitive

## Credit card stripper

The code looks for potential credit cards by using regex to identify strings of numbers that are the right length.  These numbers may contain spaces or dashes between them.

The numbers are validated and if they are valid credit card numbers they are redacted.

## Email stripper

Identifying email addresses with regex is tricky.

By default the processor uses a good regex pattern, but you might find that it is too
expensive to use.

You can call the `setRegex` function to replace the regex with something less complicated if you find your performance slipping.

The signature of this function is:

    public function setRegex(string $regex): RedactEmailProcessor 