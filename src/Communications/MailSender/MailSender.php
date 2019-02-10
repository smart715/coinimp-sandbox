<?php

namespace App\Communications\MailSender;

use App\Form\ContactUsType;
use Swift_Mailer;
use Swift_Message;

class MailSender implements MailSenderInterface
{
    /** @var Swift_Mailer */
    private $mailer;

    /** @var string */
    private $supportEmail;

    /** @var string */
    private $mailerUser;

    public function __construct(
        Swift_Mailer $mailer,
        string $supportEmail,
        string $mailerUser
    ) {
        $this->mailer = $mailer;
        $this->supportEmail = $supportEmail;
        $this->mailerUser = $mailerUser;
    }

    public function sendContactUsMail(ContactUsFormData $formData): bool
    {
        return $this->mailer->send($this->createMessage($formData)) > 0;
    }

    private function createMessage(ContactUsFormData $formData): Swift_Message
    {
        return (new Swift_Message(ContactUsType::SUBJECTS[$formData->getSubjectId()]))
            ->setBody($formData->getMessageBody(), 'text/plain')
            ->setFrom($this->mailerUser)
            ->setTo($this->supportEmail)
            ->setReplyTo([ $formData->getReplyToEmail() => $formData->getReplyToName() ]);
    }
}
