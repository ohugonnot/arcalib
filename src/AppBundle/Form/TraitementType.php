<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Traitement;

class TraitementType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attributionAt', DateType::class, [
                'widget' => 'single_text',
                "required" => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date d'attribution",
            ])
            ->add('priseAt', DateType::class, [
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date de prise",
            ])
            ->add('traitement', TextType::class, [
                "label" => "Traitement",
                "required" => false,
            ])
            ->add('numLot', TextType::class, [
                "label" => "N° /  Lot",
                "required" => false,
            ])
            ->add('peremptionAt', DateType::class, [
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date de peremption",
            ])
            ->add('nombre', IntegerType::class, [
                "label" => "Nombres",
                "required" => false,
            ])
            ->add('notes', TextareaType::class, [
                "label" => "Notes",
                "required" => false,
            ])
            ->add('verificateur', TextType::class, [
                "label" => "Verificateur",
                "required" => false,
            ])
            ->add('retour', ChoiceType::class, [
                "label" => "Retour",
                'choices'  => array(
                    'Oui' => true,
                    'Non' => false,
                ),
            ])
            ->add('retourAt', DateType::class, [
                'widget' => 'single_text',
                "required" => true,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date de retour",
            ])
            ->add('traitementRetour', TextType::class, [
                "label" => "Traitement",
                "required" => false,
            ])
            ->add('numLotRetour', TextType::class, [
                "label" => "N° /  Lot",
                "required" => false,
            ])
            ->add('nombreRetour', IntegerType::class, [
                "label" => "Nombres",
                "required" => false,
            ])
            ->add('notesRetour', TextareaType::class, [
                "label" => "Notes",
                "required" => false,
            ])
            ->add('verificateurRetour', TextType::class, [
                "label" => "Verificateur",
                "required" => false,
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
            'data_class' => Traitement::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_traitement';
    }

}
