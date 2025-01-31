<?php

namespace App\Form;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class ResettingType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'Email', 'attr' => array('pattern' => '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$'),
                'constraints' => [ new Email(['message' => 'Invalid email address.', 'checkMX' => true]) ],
            ])
            ->add('recaptcha', EWZRecaptchaType::class, [
                'label' => false,
                'constraints' => [ new RecaptchaTrue() ],
            ]);
    }

    // return '' will remove [resetting] that symfony adds in automatically built forms,
    // it would be [resetting]username but sendEmailAction in symfony's
    // ResettingController reads username only
    public function getBlockPrefix(): string
    {
        return '';
    }
}
