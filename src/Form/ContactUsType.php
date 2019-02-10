<?php

namespace App\Form;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;

class ContactUsType extends AbstractType
{
    public const SUBJECTS = [
        0 => 'General questions',
        1 => 'Payments',
        2 => 'Technical support',
        3 => 'Other',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [ 'label' => 'Name' ])
            ->add('email', EmailType::class, [
                'label' => 'Email', 'attr' => array('pattern' => '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$'),
                'constraints' => [new Email(['message' => 'Invalid email address.', 'checkMX' => true]) ],
            ])
            ->add('subject', ChoiceType::class, [
                'label' => 'Subject',
                'choices' => array_flip(self::SUBJECTS),
            ])
            ->add('body', TextareaType::class, [ 'label' => 'Message' ])
            ->add('recaptcha', EWZRecaptchaType::class, [
                'label' => false,
                'constraints' => [ new RecaptchaTrue() ],
            ])
            ->add('send', SubmitType::class, [ 'label' => 'Send' ])
        ;
    }
}
