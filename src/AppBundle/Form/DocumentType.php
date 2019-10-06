<?php

namespace AppBundle\Form;

use AppBundle\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
                "required" => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date",
                "disabled" => $options['signer'],
            ])
            ->add('type', ChoiceType::class, [
                "required" => false,
                'choices' => Document::TYPE,
                'label' => "Type",
                "disabled" => $options['signer'],
            ])
            ->add('jma', TextType::class, [
                "required" => false,
                'label' => "JMA",
                "disabled" => $options['signer'],
            ])
            ->add('auteur', TextType::class, [
                "required" => false,
                'label' => "Auteur",
                "disabled" => $options['signer'],
            ])
            ->add('description', TextareaType::class, [
                "required" => false,
                'label' => "Description",
            ])
            ->add('archive', CheckboxType::class, [
                "required" => false,
                'label' => "Archive ",
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
            'data_class' => Document::class,
            'signer' => false,
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
