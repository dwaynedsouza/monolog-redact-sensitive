<?php

namespace MuhammadGant\Monolog\Redact\Processor;

use LVR\CreditCard\Exceptions\CreditCardException;
use LVR\CreditCard\Factory;

class RedactCreditCardProcessor extends AbstractProcessor
{


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
        // we need to do three preg_match_all because after the first match is found,
        // the subsequent searches are continued on from end of the last match.
        // This means that the pattern "2 4111111111111111" would break a single regex
        $regexWithSpaces = '/(?:\d[ ]*?){13,16}/';
        preg_match_all($regexWithSpaces, $input, $spaceMatches, PREG_PATTERN_ORDER);

        $regexWithHyphen = '/(?:\d[-]*?){13,16}/';
        preg_match_all($regexWithHyphen, $input, $hyphenMatches, PREG_PATTERN_ORDER);

        $regexWithoutSpaces = '/\d{13,16}/';
        preg_match_all($regexWithoutSpaces, $input, $matches, PREG_PATTERN_ORDER);

        $regexWithSpacesAndHyphen = '/(?:\d[ -]*?){13,16}/';
        preg_match_all($regexWithSpacesAndHyphen, $input, $bothMatches, PREG_PATTERN_ORDER);

        $matchedCardNumbers = array_merge($spaceMatches[0], $matches[0], $hyphenMatches[0], $bothMatches[0]);

        return $matchedCardNumbers;
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