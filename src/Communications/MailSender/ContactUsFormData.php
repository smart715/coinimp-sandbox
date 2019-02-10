<?php

namespace App\Communications\MailSender;

class ContactUsFormData
{
    /** @var string */
    private $messageBody;

    /** @var string */
    private $subjectId;

    /** @var string */
    private $replyToEmail;

    /** @var string */
    private $replyToName;

    public function __construct(
        string $messageBody,
        string $subjectId,
        string $replyToEmail,
        string $replyToName
    ) {
        $this->messageBody = $messageBody;
        $this->subjectId = $subjectId;
        $this->replyToEmail = $replyToEmail;
        $this->replyToName = $replyToName;
    }

    public function getMessageBody(): string
    {
        return $this->messageBody;
    }

    public function getSubjectId(): int
    {
        return (int)$this->subjectId;
    }

    public function getReplyToEmail(): string
    {
        return $this->replyToEmail;
    }

    public function getReplyToName(): string
    {
        return $this->replyToName;
    }
}
