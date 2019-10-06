<?php

namespace AppBundle\Form;

use AppBundle\Entity\DocumentEssai;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentEssaiType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                "required" => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date"
            ])
            ->add('type', ChoiceType::class, [
                "required" => false,
                'choices' => DocumentEssai::TYPE,
                'label' => "Type",
            ])
            ->add('titre', TextType::class, [
                "required" => false,
                'label' => "Titre",
            ])
            ->add('details', TextareaType::class, [
                "required" => false,
                'label' => "Details",
            ])
            // ->add('file', FileType::class)
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
            'data_class' => DocumentEssai::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_document';
    }

}
