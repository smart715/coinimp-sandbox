<?php

namespace App\Tests\Communications\MailSender;

use App\Communications\MailSender\ContactUsFormData;
use App\Communications\MailSender\MailSender;
use App\Form\ContactUsType;
use PHPUnit\Framework\TestCase;
use Swift_Mailer;
use Swift_Message;

class MailSenderTest extends TestCase
{
    private const SUPPORTED_EMAIL = 'support@example.com';
    private const MAILER_USER = 'username@mailer.com';
    private const MESSAGE_BODY = 'message body';
    private const REPLY_TO_EMAIL = 'reply-to@example.com';
    private const REPLY_TO_NAME = 'Reply To';
    private const SUBJECT = 0;

    public function testSendContactUsMailCreatesCorrectSwiftMessage(): void
    {
        $mailer = $this->createMock(Swift_Mailer::class);
        $mailer->expects($this->once())->method('send')
            ->willReturnCallback(function (Swift_Message $message): int {
                return $this->assertCorrectMessage($message);
            });

        $this->createMailSender($mailer)->sendContactUsMail($this->createContactUsFormData());
    }

    public function testSendContactUsMailHandlesFailure(): void
    {
        $mailer = $this->createMock(Swift_Mailer::class);

        // zero successful recipients:
        $mailer->method('send')->willReturn(0);

        $this->assertFalse(
            $this->createMailSender($mailer)->sendContactUsMail($this->createContactUsFormData())
        );
    }

    public function testSendContactUsMailHandlesSuccess(): void
    {
        $mailer = $this->createMock(Swift_Mailer::class);

        // one successful recipient:
        $mailer->method('send')->willReturn(1);

        $this->assertTrue(
            $this->createMailSender($mailer)->sendContactUsMail($this->createContactUsFormData())
        );
    }

    private function assertCorrectMessage(Swift_Message $message): int
    {
        $this->assertEquals(self::MESSAGE_BODY, $message->getBody());
        $this->assertEquals(
            ContactUsType::SUBJECTS[self::SUBJECT],
            $message->getSUBJECT()
        );
        $this->assertEquals(
            [ self::REPLY_TO_EMAIL => self::REPLY_TO_NAME ],
            $message->getReplyTo()
        );
        $this->assertEquals([ self::SUPPORTED_EMAIL => null ], $message->getTo());
        $this->assertEquals([ self::MAILER_USER => null ], $message->getFrom());

        return 1; // one successful recipient
    }

    private function createMailSender(Swift_Mailer $mailer): MailSender
    {
        return new MailSender($mailer, self::SUPPORTED_EMAIL, self::MAILER_USER);
    }

    private function createContactUsFormData(): ContactUsFormData
    {
        return new ContactUsFormData(
            self::MESSAGE_BODY,
            self::SUBJECT,
            self::REPLY_TO_EMAIL,
            self::REPLY_TO_NAME
        );
    }
}
