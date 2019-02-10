<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class EditWalletType extends AbstractType
{
    /** @var string */
    private $pattern;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->pattern = $this->formRegex($options['walletLengths']);
        $builder
            ->add('walletAddress', TextType::class, [
                'label' => 'Wallet address',
                'empty_data' => ' ',
                'attr' => [
                    'pattern' => $this->pattern,
                ],
                'constraints' => [new Regex([
                    'pattern' => '#' . $this->pattern . '#',
                    'message' => 'Invalid wallet address' ,
                    'match' => true,
                ])]])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'walletLengths' => [],
        ]);
    }

    private function formRegex(array $walletLengths): string
    {
        $pattern = '';
        foreach ($walletLengths as $length) {
            $pattern .= '|\w{'.$length.'}';
        }
        return ltrim($pattern, '|');
    }
}
