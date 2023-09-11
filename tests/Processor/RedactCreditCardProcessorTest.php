<?php

use \MuhammadGant\Monolog\Redact\Processor\RedactCreditCardProcessor;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class RedactCreditCardProcessorTest extends TestCase
{
    /**
     * @var TestHandler
     */
    private $handler;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RedactCreditCardProcessor
     */
    private $processor;

    public $validCreditCards = [
        ['3782 8224 6310 005'], ['37144963 5398 431'], ['3787-34493671000'],
        ['5610-5910 8101 8250'], ['3056 9309025 904'], ['38520000-023 237'],
        ['6011 1111-1111-1117'], ['6011 0009 90139424'], ['3530-11133330-0000'],
        ['3566-0020 2036-0505'], ['5555 5555 55554444'], ['510510510510-5100'],
        ['4111-1111-1111-1111'], ['4012888888881881'], ['4222 2222 2222 2'],
    ];

    /**
     * @return array
     * @see https://www.paypalobjects.com/en_AU/vhelp/paypalmanager_help/credit_card_numbers.htm
     */
    public function creditCardNumberProvider()
    {
        return $this->validCreditCards;
    }

    /**
     * These numbers are the right length but are not valid card numbers
     */
    public function invalidCardNumberProvider()
    {
        return [
            ['1111 2222 3333 4444'], ['1111-2222-3333-4444'],
            ['1111-2222 3333-4444'], ['12344578-3333-4444'],
            ['abcd-efgh-ijkl-mnop'], ['']
        ];
    }

    public function setUp(): void
    {
        $this->processor = new RedactCreditCardProcessor();
        $this->handler = new TestHandler();
        $this->logger = new Logger('test', [$this->handler]);
        $this->logger->pushProcessor($this->processor);
        parent::setUp();
    }

    /**
     * Do we find credit cards present in a long string?
     * This is a weak test, but the regex is weak
     * @throws Exception
     */
    public function testFindAllCreditCardNumbers()
    {
        $message = '';

        foreach ($this->validCreditCards as $validCreditCard) {

            $message .= bin2hex(random_bytes(3)) . $validCreditCard[0];

        }

        $matches = $this->processor->findPotentialCardNumbers($message);

        $this->assertGreaterThan(count($this->validCreditCards), count($matches));
    }

    /**
     * @dataProvider creditCardNumberProvider
     */
    public function testCreditCardIsRedactedInLogMessage(string $creditCardNumber)
    {
        $message = 'Remove this credit card number ' . $creditCardNumber . ' please';

        $data = [
            'card-number' => [
                'example' => $creditCardNumber
            ]
        ];

        $this->logger->log(Logger::DEBUG, $message, $data);

        $records = $this->handler->getRecords();

        $this->assertEquals('Remove this credit card number [removed] please', $records[0]['message']);

        $this->assertEquals(
            [
                'card-number' => [
                    'example' => '[removed]'
                ]
            ],
            $records[0]['context']
        );
    }

    public function testChangeRedactedString()
    {
        $message = 'Card 38520000-023 237';

        $redacted = bin2hex(random_bytes(5));

        $this->processor->setRedactedString($redacted);

        $data = [
            'card-number' => [
                'example' => '6011 1111-1111-1117'
            ]
        ];

        $this->logger->log(Logger::DEBUG, $message, $data);

        $records = $this->handler->getRecords();

        $this->assertEquals('Card ' . $redacted , $records[0]['message']);

        $this->assertEquals(
            [
                'card-number' => [
                    'example' => $redacted
                ]
            ],
            $records[0]['context']
        );
    }

    public function testMultipleCards()
    {
        $message = 'Card 1 4944-7913-0239-6655 Card 2 4168950260090804';

        $redacted = bin2hex(random_bytes(5));

        $this->processor->setRedactedString($redacted);

        $this->logger->log(Logger::DEBUG, $message);

        $records = $this->handler->getRecords();

        $this->assertEquals('Card 1 ' . $redacted . ' Card 2 ' . $redacted, $records[0]['message']);
    }

    public function testSkippingNoCardsFound()
    {
        $message = "Ceci n'est pas une credit card";

        $data = [
            'card-number' => [
                'example' => 'The treachery of credit cards'
            ]
        ];

        $this->logger->log(Logger::DEBUG, $message, $data);

        $records = $this->handler->getRecords();

        $this->assertEquals($message, $records[0]['message']);

        $this->assertEquals($data, $records[0]['context']);
    }

    /**
     * @dataProvider invalidCardNumberProvider
     */
    public function testStringContainsInvalidCardNumbers(string $invalidCardNumber)
    {
        $this->assertFalse($this->processor->isValidCreditCardNumber($invalidCardNumber));
    }
}