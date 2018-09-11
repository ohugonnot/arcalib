<?php

namespace AppBundle\Form;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Service;
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

class EssaisType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ["required" => false, 'label' => 'ESSAI'])
            ->add('titre', TextareaType::class, ["required" => false, 'label' => 'Titre'])
            ->add('numeroInterne', TextType::class, ["required" => false, 'label' => 'N° Interne'])
            ->add('statut', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'choices' => Essais::STATUT))
            ->add('numeroCentre', TextType::class, ["required" => false, 'label' => 'N° Centre'])
            ->add('dateOuv', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Mise en place"))
            ->add('dateFinInc', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Fin des inclusions"))
            ->add('dateClose', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Cloture"))
            ->add('typeEssai', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'label' => "Type étude",
                'choices' => Essais::TYPE))
            ->add('stadeEss', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'label' => "Phase",
                'choices' => Essais::PHASE))
            ->add('typeProm', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'label' => "Type promoteur",
                'choices' => Essais::PROM))
            ->add('autoProm', ChoiceType::class, array(
                "expanded" => true,
                "required" => false,
                "multiple" => false,
                'label' => "Promotion Interne/ Externe",
                'choices' => Essais::AUTO_PROM))
            ->add('prom', TextType::class, ["required" => false, 'label' => 'Promoteur'])
// Contact

            ->add('contactNom', TextType::class, ["required" => false, 'label' => 'Nom  du contact'])
            ->add('contactMail', TextType::class, ["required" => false, 'label' => 'Mail'])
            ->add('contactTel', TextType::class, ["required" => false, 'label' => 'Téléphone'])
            // Divers
            ->add('ecrfLink', TextType::class, ["required" => false, 'label' => 'Lien Ecrf'])
            ->add('notes')
            ->add('urcGes', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'label' => "Gestion URC",
                'choices' => Essais::URCGES))
            ->add('sigrec')
            ->add('sigaps')
            ->add('emrc')
            ->add('eudraCtNd')
            ->add('ctNd')
            ->add('cancer')
            ->add('typeConv', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'label' => "Type convention",
                'choices' => Essais::TYPE_CONV))
            ->add('dateSignConv', DateType::class, array(
                'widget' => 'single_text',
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'autocomplete' => 'off'],
                'label' => "Signature convention"))
            ->add('numEudract', TextType::class, ["required" => false, 'label' => 'N° EudraCT'])
            ->add('numCt', TextType::class, ["required" => false, 'label' => 'N° Clinical Trial'])
            ->add('intLink', TextType::class, ["required" => false, 'label' => 'Lien Serveur interne'])
            ->add('tagsString', TextType::class, ["required" => false, 'label' => 'Tags', "attr" => ["data-role" => ""], "mapped" => false])
// Liens vers Arc       
            ->add('arc', EntityType::class, array(
                'class' => Arc::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.nomArc', 'ASC');
                },
                'choice_label' => 'nomArc',
                'required' => false,
            ))
            ->add('services', EntityType::class, array(
                'class' => Service::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.nom', 'ASC');
                },
                'choice_label' => 'nom',
                'required' => false,
                'multiple' => true,
            ))
// Liens vers Medecin
            ->add('medecin', EntityType::class, array(
                'class' => Medecin::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.nom', 'ASC');
                },
                'choice_label' => 'NomPrenom',
                'required' => false,
            ))
            /**->add('inclusions', EntityType::class, array(
             * // query choices from this entity
             * 'class' => 'AppBundle:Inclusion',
             * 'query_builder' => function ($er) {
             * return $er->createQueryBuilder('i')
             * ->orderBy('i.id', 'ASC');
             * },
             * // use the User.username property as the visible option string
             * 'choice_label' => 'id',
             *
             * // used to render a select box, check boxes or radios
             * 'multiple' => true,
             * 'by_reference' => false,
             * 'required' => false,
             * // 'expanded' => true,
             * ))*/

            ->add('objectif')
// FORMULAIRE DETAIL: A sortir du formulaire principal: TODO

            ->add('detail', EssaiDetailType::class)
// BOUTONS

            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ))
            ->add('inclure', SubmitType::class, array(
                'label' => "Enregistrer et inclure",
            ));
        // ->add('arc');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Essais::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_essais';
    }


}
