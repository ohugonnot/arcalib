<?php

namespace AppBundle\Form;

use AppBundle\Entity\Annuaire;
use AppBundle\Entity\Essais;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnuaireType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('fonction', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "label" => "Fonction",
                "multiple" => false,
                'choices' => Annuaire::FONCTION))
            ->add('societe', TextType::class, ['label' => 'Société'])
            ->add('mail', EmailType::class, ["required" => false, 'label' => 'Adresse email'])
            ->add('telephone', TextType::class, ["required" => false, 'label' => 'Téléphone'])
            ->add('portable', TextType::class, ["required" => false, 'label' => 'Téléphone Port.'])
            ->add('fax', TextType::class, ["required" => false, 'label' => 'Fax'])
            ->add('notes')
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
            ->add('autre', TextType::class, ["required" => false, 'label' => 'Autre'])
            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ));;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Annuaire::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_annuaire';
    }

}
