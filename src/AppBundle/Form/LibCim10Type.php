<?php

namespace AppBundle\Form;

use AppBundle\Entity\LibCim10;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibCim10Type extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cim10code', TextType::class, ['required' => false, 'label' => 'CIM10Code'])
            ->add('libCourt', TextType::class, ['required' => false, 'label' => 'Label Court'])
            ->add('libLong', TextareaType::class, ['required' => false, 'label' => 'Label Long'])
            ->add('utile', CheckboxType::class, ['required' => false, 'label' => 'Utile'])
            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ));
    }

    /**
     * {@inheritdoc}
     * */
     public function configureOptions(OptionsResolver $resolver)
     {
        $resolver->setDefaults(array(
            'data_class' => LibCim10::class
        ));
     }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_libcim10';
    }


}
