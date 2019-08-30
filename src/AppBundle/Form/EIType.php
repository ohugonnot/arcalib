<?php

namespace AppBundle\Form;

use AppBundle\Entity\CTCAEGrade;
use AppBundle\Entity\CTCAESoc;
use AppBundle\Entity\CTCAETerm;
use AppBundle\Entity\EI;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EIType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                "required" => false,
                'choices' => EI::TYPE,
                'label' => "Type",
            ])
            ->add('siEIG', ChoiceType::class, [
                "required" => false,
                'choices' => EI::SI_EIG,
                'label' => "Si EIG",
            ])
            ->add('debutAt',DateType::class, [
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Début"
            ])
            ->add('diagnostic', TextType::class, [
                "required" => false,
            ])
            ->add('evolution', ChoiceType::class, [
                "required" => false,
                'choices' => EI::EVOLUTION,
                'label' => "Evolution",
            ])
            ->add('finAt', DateType::class, [
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Fin"
            ])
            ->add('siDeces', ChoiceType::class, [
                "required" => false,
                'choices' => EI::SI_DECES,
                'label' => "Si décès",
            ])
            ->add('texteDC', TextType::class, [
                "required" => false,
                'label' => "Cause décès (en texte)",
            ])
            ->add('details', TextareaType::class, [
                "required" => false,
                'label' => "Détails de l'EI/EIG",
            ])
            ->add('surcouts', ChoiceType::class, [
                "required" => false,
                'choices' => EI::SURCOUTS,
                'label' => "Surcouts",
            ])
            ->add('suivi', ChoiceType::class, [
                "required" => true,
                'choices' => EI::SUIVI,
                'label' => "Statut",
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'label' => "Référents",
            ])
            ->add('soc', EntityType::class, [
                'class' => CTCAESoc::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('soc')
                        ->orderBy('soc.nom', 'ASC');
                },
                'choice_label' => 'nom',
                'required' => false,
                'label' => "Classe",
            ])
            ->add('term', EntityType::class, [
                'class' => CTCAETerm::class,
                'choice_label' => 'nom',

            ])
            ->add('grade', EntityType::class, [
                'class' => CTCAEGrade::class,
                'choice_label' => function (CTCAEGrade $grade) {
                    return $grade->getGrade().' - '.$grade->getNom();
                    
                }
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
            'data_class' => EI::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_ei';
    }


}
