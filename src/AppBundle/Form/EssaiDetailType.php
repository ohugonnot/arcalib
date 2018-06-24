<?php

namespace AppBundle\Form;

use AppBundle\Entity\EssaiDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EssaiDetailType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('crInc', TextareaType::class, ["required" => false, 'label' => "Critéres d'inclusion"])// ToDO
            ->add('crNonInc', TextareaType::class, ["required" => false, 'label' => 'Critéres de NON inclusion'])
            ->add('objectif', TextareaType::class, ["required" => false, 'label' => "Objectif de l'etude"])
            ->add('calendar', TextareaType::class, ["required" => false, 'label' => 'Calendrier']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => EssaiDetail::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_essaidetail';
    }


}
