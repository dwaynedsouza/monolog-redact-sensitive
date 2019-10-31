<?php
namespace AndyBeak\Monolog\Redact\Interfaces;

interface ProcessorInterface
{
    public function __invoke(array $record): array;

    public function flattenRecord(array $record): string;

    public function redactString(string $string): string;
}