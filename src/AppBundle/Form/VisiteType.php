<?php

namespace AppBundle\Form;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Visite;
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

class VisiteType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Date visite"
            ))
            ->add('type', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'choices' => Visite::TYPE))
            ->add('calendar', TextType::class, ["required" => false, 'label' => 'N° visite(J/M/A)'])
            ->add('statut', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'choices' => Visite::STATUT))
            ->add('fact')
            ->add('numfact', TextType::class, ["required" => false, 'label' => 'N°facture'])
            ->add('note', TextareaType::class, ["required" => false, 'label' => 'Notes'])
            ->add('arc', EntityType::class, array(
                'class' => Arc::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.nomArc', 'ASC');
                },
                'choice_label' => 'nomArc',
                'required' => false,
            ))
            ->add('inclusion', EntityType::class, array(
                'class' => Inclusion::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.id', 'ASC');
                },
                'choice_label' => 'id',
                'required' => true,
            ))
//--------------------------------------------------------------BOUTONS--------------------------------------------------------------
            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ));

//-----------------------------------------------------------FIN--BOUTONS--------------------------------------------------------------
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Visite::class
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_visite';
    }


}
