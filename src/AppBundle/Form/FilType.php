<?php

namespace AppBundle\Form;

use AppBundle\Entity\Fil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'label' => "Type",
                'choices' => Fil::TYPES))
            ->add('texte', TextareaType::class, ["required" => false, 'label' => 'Commentaire'])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                "required" => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date"
            ])
            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Fil::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_fil';
    }
}
