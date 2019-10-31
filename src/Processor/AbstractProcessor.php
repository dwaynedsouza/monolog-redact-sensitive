<?php
namespace AndyBeak\Monolog\Redact\Processor;

use AndyBeak\Monolog\Redact\Interfaces\ProcessorInterface;

abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * @var string
     */
    protected $redactedString = '[removed]';

    abstract public function redactString(string $string): string;

    /**
     * Flatten the string and run the redaction routine on it
     * @param array $record
     * @return array
     */
    public function __invoke(array $record): array
    {
        $flatString = $this->flattenRecord($record);

        $redactedString = $this->redactString($flatString);

        if ($redactedString === $flatString) {

            return $record;

        }

        return json_decode($redactedString, true);
    }

    /**
     * Return a flattened version of the array so that we can search through everything without
     * needing to recurse or iterate.
     * @param array $record
     * @return string
     */
    public function flattenRecord(array $record): string
    {
        return json_encode($record);
    }

    /**
     * @param string $string
     */
    public function setRedactedString(string $string): void
    {
        $this->redactedString = $string;
    }
}