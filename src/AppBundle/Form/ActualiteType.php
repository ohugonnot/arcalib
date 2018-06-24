<?php

namespace AppBundle\Form;

use AppBundle\Entity\Actualite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActualiteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                // do not render as type="date", to avoid HTML5 date pickers
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                // add a class that can be selected in JavaScript
                // 'attr' => ['class' => 'js-datepicker'],
                'label' => "Date"
            ))
            ->add('enabled', CheckboxType::class, ['required' => false, 'label' => 'Activée'])
            ->add('text', TextareaType::class, ['required' => false, 'label' => 'Contenu'])
            ->add('titre', TextType::class, ["required" => true, 'label' => 'Titre'])
            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ));;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Actualite::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_actualite';
    }
}
