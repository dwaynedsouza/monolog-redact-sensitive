<?php
namespace AndyBeak\Monolog\Redact\Processor;

class RedactEmailProcessor extends AbstractProcessor
{
    /**
     * The regex at http://emailregex.com/ causes PREG_BACKTRACK_LIMIT_ERROR in many situations
     * This is a more simple (but less accurate) check
     * @var string
     */
    private $emailRegex = "([a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`\"\"{|}~-]+)*(@|\sat\s)(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(\.|\"\"\sdot\s))+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)";

    /**
     * Uses regex (which is imperfect) to identify and remove email addresses
     * @param string $input
     * @return string
     */
    public function redactString(string $input): string
    {
        if (false === strpos($input, '@')) {

            return $input;

        }

        $redacted = preg_replace("/{$this->emailRegex}/", '[removed]', $input);

        return $redacted ?? $input;
    }

    /**
     * This allows you to use a less accurate but faster regex string to improve
     * @param string $regex
     * @return RedactEmailProcessor
     */
    public function setRegex(string $regex): RedactEmailProcessor
    {
        $this->emailRegex = $regex;

        return $this;
    }
}