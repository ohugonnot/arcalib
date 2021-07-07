<?php

namespace AppBundle\Form;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Service;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArcType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)

        // ------------------------------------------Formulaire-----------------------------------------------------
    {
        $builder
            ->add('nomArc', TextType::class, ['label' => 'Nom'])
            ->add('prenomArc', TextType::class, ['label' => 'Prénom','required'=>false])
            ->add('datIn', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'label' => "Date d'arrivée"
            ))
            ->add('datOut', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'label' => "Date de départ"
            ))
            ->add('iniArc', TextType::class, ["required" => false, 'label' => 'Initiales NOM-PR'])
            ->add('service', EntityType::class, array(
                'class' => Service::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.nom', 'ASC');
                },
                'choice_label' => 'nom',
                'required' => false,
            ))
            ->add('dect', TextType::class, ["required" => false, 'label' => 'DECT'])
            ->add('tel', TextType::class, ["required" => false, 'label' => 'Téléphone'])
            ->add('mail', EmailType::class, ["required" => false, 'label' => 'Adresse email'])
            // ------------------------------------------Bouton Envoi-----------------------------------------------------

            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ));

        // -----------------------------------------ENd -Bouton Envoi-----------------------------------------------------
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Arc::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_arc';
    }
}
