<?php

namespace AppBundle\Form;

use AppBundle\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
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
                // do not render as type="date", to avoid HTML5 date pickers
                "required" => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                // add a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
                'label' => "Date"
            ])
            ->add('type', ChoiceType::class, [
                "required" => false,
                'choices' => Document::TYPE,
                'label' => "Type",
            ])
            ->add('jma', TextType::class, [
                "required" => false,
                'label' => "JMA",
            ])
            ->add('description', TextareaType::class, [
                "required" => false,
                'label' => "Description",
            ])
            ->add('auteur', TextType::class, [
                "required" => false,
                'label' => "Auteur",
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
            'data_class' => Document::class
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
