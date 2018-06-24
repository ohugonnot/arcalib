<?php

namespace AppBundle\Form;

use AppBundle\Entity\Todo;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TodoType extends AbstractType
{

    private $tokenStorage;

    private $container;

    /**
     * TodoType constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param ContainerInterface $container
     */
    public function __construct(TokenStorageInterface $tokenStorage, ContainerInterface $container)
    {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $user = $this->tokenStorage->getToken()->getUser();

        $formData = $builder->getData();
        $auteur = $formData->getAuteur();


        if (
            $user != $auteur
            && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            && $formData->getId() != null
        ) {

            $builder
                ->add('titre', TextType::class, ["disabled" => true])
                ->add('texte', TextareaType::class, ["required" => false, "disabled" => true, "label" => "Action"])
                ->add('importance', ChoiceType::class, array(
                    "expanded" => false,
                    "required" => true,
                    "multiple" => false,
                    'choices' => Todo::IMPORTANCE,
                    'choice_label' => function ($IMPORTANCE) {
                        return $IMPORTANCE;
                    },
                    "disabled" => true
                ))
                ->add('alerte', CheckboxType::class, array(
                    'label' => 'Alerte',
                    'required' => false,
                    "disabled" => true,
                ))
                ->add('dateAlerte', DateType::class, array(
                    'widget' => 'single_text',
                    // do not render as type="date", to avoid HTML5 date pickers
                    "required" => false,
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    // add a class that can be selected in JavaScript
                    'attr' => ['class' => 'js-datepicker'],
                    'label' => "Date de l'alerte",
                    "disabled" => true
                ))
                ->add('dateFin', DateType::class, array(
                    'widget' => 'single_text',
                    // do not render as type="date", to avoid HTML5 date pickers
                    "required" => false,
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    // add a class that can be selected in JavaScript
                    'attr' => ['class' => 'js-datepicker'],
                    'label' => "Date de l'échéance",
                    "disabled" => true
                ));

        } else {

            $builder
                ->add('titre', TextType::class)
                ->add('texte', TextareaType::class, ["required" => false, "label" => "Action"])
                ->add('importance', ChoiceType::class, array(
                    "expanded" => false,
                    "required" => true,
                    "multiple" => false,
                    'choices' => Todo::IMPORTANCE,
                ))
                ->add('alerte', CheckboxType::class, array(
                    'label' => 'Alerte',
                    'required' => false,
                ))
                ->add('dateAlerte', DateType::class, array(
                    'widget' => 'single_text',
                    // do not render as type="date", to avoid HTML5 date pickers
                    "required" => false,
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    // add a class that can be selected in JavaScript
                    'attr' => ['class' => 'js-datepicker'],
                    'label' => "Date de l'alerte"
                ))
                ->add('destinataires', EntityType::class, array(

                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.username', 'ASC');
                    },
                    'label' => 'Destinataires',
                    'choice_label' => function (User $user) {
                        return ucfirst($user->getUsername());
                    },
                    'expanded' => true,
                    'multiple' => true,
                    'required' => true,

                    //--- ajout de la class pour activer select2 -> SELECT avec recherche d'un patient
                    // 'attr' => ["class" => "js-select2"],
                    'attr' => ["class" => "bloc-notes-destinataires"],
                ))
                ->add('dateFin', DateType::class, array(
                    'widget' => 'single_text',
                    // do not render as type="date", to avoid HTML5 date pickers
                    "required" => false,
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    // add a class that can be selected in JavaScript
                    'attr' => ['class' => 'js-datepicker'],
                    'label' => "Date de l'échéance"
                ));

        }

        $builder
            ->add('resolution', TextareaType::class, ["required" => false, "label" => "Commentaires Résolution (optionel)"])
            ->add('niveauResolution', ChoiceType::class, array(
                "expanded" => false,
                "required" => true,
                "multiple" => false,
                'choices' => Todo::NIVEAU_RESOLUTION,
            ))
            ->add('auteur', EntityType::class, array(

                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
                'label' => 'Auteur',
                'choice_label' => 'username',
                'required' => true,

                //--- ajout de la class pour activer select2 -> SELECT avec recherche d'un patient  
                'attr' => ["class" => "js-select2"],
                "disabled" => true,
            ))
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
            'data_class' =>  Todo::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_todo';
    }


}
