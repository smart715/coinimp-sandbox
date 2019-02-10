<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmitExtendedType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'name' => 'action-submit & action-submit-extended',
            'attr' => [
                'firstlabel' => 'Save',
                'secondlabel' => 'Save & add new',
                'feature' => 'data-submit-and-close',
                'cancelbutton' => true,
            ],
            'mapped' => false,
            'label'=> false,
        ]);
    }
}
