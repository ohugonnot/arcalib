<?php

namespace AppBundle\Form;

use AppBundle\Entity\LibCim10;
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

class PatientType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('nomNaissance', TextType::class, ['label' => 'Nom de Naissance'])
            ->add('prenom', TextType::class, ['label' => ' Prénom'])
            ->add('initial', TextType::class, ['label' => ' Initiales', 'disabled' => true])

            ->add('datNai', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date de Naissance"
            ))
            ->add('sexe', ChoiceType::class, array(
                "expanded" => true,
                "required" => false,
                "multiple" => false,
                'choices' => Patient::SEXE))
            ->add('ipp', TextType::class, ['required' => false, 'label' => 'IPP'])
            ->add('datDiag', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date de diagnostic"
            ))
            ->add('cancer')
            ->add('libCim10', EntityType::class, array(
                'class' => LibCim10::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->where('l.utile = true')
                        ->orderBy('l.cim10code', 'ASC');
                },
                'choice_label' => 'libCourt',
                'required' => false,
                'label' => "Code CIM 10",
                'attr' => ["class" => "js-select2"],
            ))
            ->add('txtDiag', TextareaType::class, ['required' => false, 'label' => 'Diagnostic (texte)'])
            ->add('datLast', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'format' => 'dd/MM/yyyy',
                'label' => "Date dernières nouvelles"
            ))
            ->add('evolution', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'choices' => Patient::EVOLUTION,
                'label' => "Evolution",
            ))
            ->add('deces', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'choices' => Patient::DECES))
            ->add('datDeces', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'format' => 'dd/MM/yyyy',
                'label' => "Date décès"
            ))
            ->add('medecin', EntityType::class, array(
                'class' => Medecin::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.nom', 'ASC');
                },
                'choice_label' => 'NomPrenom',
                'required' => false,
                'label' => "Médecin référent"))

            ->add('memo', TextareaType::class, ['required' => false, 'label' => 'Notes'])
            // *****************************boutons***********************************************

            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ))
            ->add('inclure', SubmitType::class, array(
                'label' => "Inclure ",
            ));
    }

// *****************************Fin des variables***********************************************

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Patient::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_patient';
    }
// *****************************Fin***********************************************

}
