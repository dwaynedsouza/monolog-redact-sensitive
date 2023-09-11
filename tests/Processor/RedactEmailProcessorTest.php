<?php

use \MuhammadGant\Monolog\Redact\Processor\RedactEmailProcessor;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class RedactEmailProcessorTest extends TestCase
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
     * @var RedactEmailProcessor
     */
    private $processor;

    /**
     * @var array
     */
    public $validEmailAddresses = [
        ['simple@example.com'], ["!def!xyz%abc@example.com"], ["user+mailbox@example.com"],
        ['peter.piper@example.com'], ["dclo@us.ibm.com"], ["!def!xyz%abc@example.com"]
    ];

    /**
     * @return array
     */
    public function validEmailProvider()
    {
        return $this->validEmailAddresses;
    }

    public function setUp(): void
    {
        $this->processor = new RedactEmailProcessor();
        $this->handler = new TestHandler();
        $this->logger = new Logger('test', [$this->handler]);
        $this->logger->pushProcessor($this->processor);
        parent::setUp();
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function testEmailIsRedactedInLogMessage(string $email)
    {

        $message = 'Remove this email address ' . $email . ' please';

        $data = [
            'contact-details' => [
                'example' => $email
            ]
        ];

        $this->logger->log(Logger::DEBUG, $message, $data);

        $records = $this->handler->getRecords();

        $this->assertEquals('Remove this email address [removed] please', $records[0]['message']);

        $this->assertEquals(
            [
                'contact-details' => [
                    'example' => '[removed]'
                ]
            ],
            $records[0]['context']
        );
    }

    public function testSkippingNoEmails()
    {
        $message = "Ceci n'est pas une email";

        $data = [
            'contact-details' => [
                'example' => 'The Treachery of Email'
            ]
        ];

        $this->logger->log(Logger::DEBUG, $message, $data);

        $records = $this->handler->getRecords();

        $this->assertEquals($message, $records[0]['message']);

        $this->assertEquals($data, $records[0]['context']);
    }

    public function testSetRegex()
    {
        $processor = $this->processor->setRegex('test');

        $this->assertSame($processor, $this->processor);
    }

}