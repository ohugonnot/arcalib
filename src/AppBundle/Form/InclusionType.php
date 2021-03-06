<?php

namespace AppBundle\Form;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
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

class InclusionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('patient', EntityType::class, array(
                'class' => Patient::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.nom', 'ASC');
                },
                'choice_label' => 'nomPrenom',
                'required' => false,
                'attr' => ["class" => "js-select2"],
            ))
            ->add('essai', EntityType::class, array(
                'class' => Essais::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.nom', 'ASC');
                },
                'choice_label' => 'nom',
                'required' => false,
                'attr' => ["class" => "js-select2"],
            ))
            ->add('statut', ChoiceType::class, array(
                'choices' => Inclusion::STATUT,
                'label' => "Statut",
            ))
            ->add('datScr', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date Screen"
            ))
            ->add('datCst', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date Consentement"
            ))
            ->add('datInc', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date Inclusion"
            ))
            ->add('datRan', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date Randomisation"
            ))
            ->add('datJ0', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date J0"
            ))
            ->add('numInc', TextType::class, ["required" => false, 'label' => 'N° inclusion'])
            ->add('braTrt', TextType::class, ["required" => false, 'label' => 'Bras TT'])
            ->add('datOut', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date sortie d'etude"
            ))
            ->add('motifSortie', ChoiceType::class, array(
                'choices' => Inclusion::MOTIF_SORTIE,
                'label' => "Motif de la sortie",
                'attr' => ["class" => "js-select2"],
            ))
            ->add('note', TextareaType::class, ["required" => false, 'label' => 'Notes'])
// -------------Variables liés a d'autres tables------------------------------------------------------------------------

            ->add('medecin', EntityType::class, array(
                'class' => Medecin::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.nom', 'ASC');
                },
                'choice_label' => 'nomPrenom',
                'required' => false,
                'label' => "Médecin référent",
                'attr' => ["class" => "js-select2"],
            ))
            ->add('arc', EntityType::class, array(
                'class' => Arc::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.nomArc', 'ASC');
                },
                'choice_label' => 'nomPrenom',
                'required' => false,
                'label' => "ARC référent",
                'attr' => ["class" => "js-select2"],
            ))
            /**->add('visites', EntityType::class, array(
             * // query choices from this entity
             * 'class' => 'AppBundle:Visite',
             * 'query_builder' => function ($er) {
             * return $er->createQueryBuilder('v')
             * ->orderBy('v.date', 'ASC');
             * },
             * // use the User.username property as the visible option string
             * 'choice_label' => 'id',
             * 'required' => false,
             * // used to render a select box, check boxes or radios
             * 'by_reference' => false,
             * 'multiple' => true,
             * // 'expanded' => true,
             * ))*/
// -----------------------------------------Boutons------------------------------------------------------------------------

            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ))
            ->add('addVisite', SubmitType::class, array(
                'label' => "Ajouter une visite",
            ));
    }

// -------------------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Inclusion::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_inclusion';
    }

}
