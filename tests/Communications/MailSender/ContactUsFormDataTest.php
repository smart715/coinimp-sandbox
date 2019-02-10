<?php

namespace App\Tests\Communications\MailSender;

use App\Communications\MailSender\ContactUsFormData;
use PHPUnit\Framework\TestCase;

class ContactUsFormDataTest extends TestCase
{
    public function testGetters(): void
    {
        $body = 'Thanks for the awesome service!';
        $subjectId = 0;
        $email = 'lucky.guy@example.com';
        $name = 'Lucky Guy';

        $contactUs = new ContactUsFormData($body, $subjectId, $email, $name);

        $this->assertEquals($body, $contactUs->getMessageBody());
        $this->assertEquals($subjectId, $contactUs->getSubjectId());
        $this->assertEquals($email, $contactUs->getReplyToEmail());
        $this->assertEquals($name, $contactUs->getReplyToName());
    }
}
