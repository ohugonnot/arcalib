<?php

namespace AppBundle\Form;

use AppBundle\Entity\Medecin;
use AppBundle\Entity\Service;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedecinType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('service', EntityType::class, array(
                // query choices from this entity
                'class' => Service::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.nom', 'ASC');
                },
                // use the User.username property as the visible option string
                'choice_label' => 'nom',
                'required' => false,
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ))
            ->add('dateEntre', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker'],
                'label' => "Date d'entrée"
            ))
            ->add('dateSortie', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker'],
                'label' => "Date de départ"
            ))
            ->add('dect', TextType::class, ["required" => false, 'label' => 'DECT'])
            ->add('email', EmailType::class, ["required" => false, 'label' => 'Adresse Email'])
            ->add('portable', TextType::class, ["required" => false, 'label' => 'Portable'])
            ->add('note', TextareaType::class, ["required" => false, 'label' => 'Notes'])
            ->add('secNom', TextType::class, ["required" => false, 'label' => 'Secrétaire(s)'])
            ->add('secTel', TextType::class, ["required" => false, 'label' => 'Téléphone secrétariat'])
            ->add('numSiret', TextType::class, ["required" => false, 'label' => 'N° SIRET'])
            ->add('numSigaps', TextType::class, ["required" => false, 'label' => 'N° SIGAPS'])
            ->add('numOrdre', TextType::class, ["required" => false, 'label' => 'N° ORDRE'])
            ->add('numRpps', TextType::class, ["required" => false, 'label' => 'N° RPPS'])
            ->add('merri', TextType::class, ["required" => false, 'label' => 'N° MERRI'])
            ->add('envoyer', SubmitType::class, array(
                'label' => '',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Medecin::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_medecin';
    }
}