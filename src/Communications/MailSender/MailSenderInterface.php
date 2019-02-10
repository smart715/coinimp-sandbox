<?php

namespace App\Communications\MailSender;

interface MailSenderInterface
{
    /**
     * @param ContactUsFormData $formData
     * @return bool True if email was successfully sent, false otherwise.
     */
    public function sendContactUsMail(ContactUsFormData $formData): bool;
}
