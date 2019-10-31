<?php

namespace AndyBeak\Monolog\Redact\Processor;

use LVR\CreditCard\Exceptions\CreditCardException;
use LVR\CreditCard\Factory;

class RedactCreditCardProcessor extends AbstractProcessor
{

    /**
     * @var string
     */
    private $cardRegex = '/(?:\d[ -]*?){13,16}/';

    /**
     * @param string $input
     * @return string
     */
    public function redactString(string $input): string
    {
        $potentialCardNumbers = $this->findPotentialCardNumbers($input);

        if (count($potentialCardNumbers) === 0) {

            return $input;

        }

        $redactedString = $input;

        foreach ($potentialCardNumbers as $potentialCardNumber) {

            if ($this->isValidCreditCardNumber($potentialCardNumber)) {

                $redactedString = str_replace($potentialCardNumber, $this->redactedString, $redactedString);

            }

        }

        return $redactedString;
    }


    /**
     * @param string $input
     * @return array
     */
    public function findPotentialCardNumbers(string $input): array
    {
        preg_match_all($this->cardRegex, $input, $matches, PREG_PATTERN_ORDER);

        $potentialCardNumbers = $matches[0];

        return $potentialCardNumbers;
    }

    /**
     * @param string $cardNumber
     * @return bool
     * @see \LVR\CreditCard\CardNumber::passes
     */
    public function isValidCreditCardNumber(string $cardNumber): bool
    {
        try {

            $cardNumber = str_replace([' ', '-'], '', $cardNumber);

            return Factory::makeFromNumber($cardNumber)->isValidCardNumber();

        } catch (CreditCardException $ex) {

            return false;

        }
    }

}