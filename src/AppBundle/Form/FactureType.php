<?php

namespace AppBundle\Form;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Facture;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactureType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                // do not render as type="date", to avoid HTML5 date pickers
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                // add a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
                'label' => "Date Facture"
            ))
            ->add('numero', TextType::class, ["required" => false, 'label' => 'N° Facture'])
            ->add('payeur', TextType::class, ["required" => false, 'label' => 'Payeur'])
            ->add('receveur', TextType::class, ["required" => false, 'label' => 'Receveur'])
            ->add('montantHt', NumberType::class, ["required" => false, 'label' => 'Montant HT (€)'])
            ->add('montantTtc', NumberType::class, ["required" => false, 'label' => 'Montant TTC (€)'])
            ->add('taxTVA', NumberType::class, ["required" => false, 'label' => 'Taux TVA %'])
            ->add('TVA', NumberType::class, ["required" => false, 'label' => 'TVA (€)'])
            ->add('type', ChoiceType::class, array(
                "required" => false,
                'choices' => Facture::TYPE,
                'label' => "Type",

            ))
            ->add('creditDebit', ChoiceType::class, array(
                "required" => false,
                'choices' => Facture::CREDIT_DEBIT,
                'label' => "Crédit/Débit",
            ))
            ->add('projet', TextType::class, ["required" => false, 'label' => 'Projet'])
            ->add('numInterne', TextType::class, ["required" => false, 'label' => 'N° Interne'])
            ->add('statut', ChoiceType::class, array(
                "expanded" => false,
                "required" => false,
                "multiple" => false,
                'choices' => Facture::STATUT))
            ->add('dateEncaissement', DateType::class, array(
                'widget' => 'single_text',
                // do not render as type="date", to avoid HTML5 date pickers
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                // add a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
                'label' => "Encaissable le"
            ))
            ->add('dateCaisse', DateType::class, array(
                'widget' => 'single_text',
                // do not render as type="date", to avoid HTML5 date pickers
                "required" => false,
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                // add a class that can be selected in JavaScript
                'attr' => ['class' => 'js-datepicker'],
                'label' => "Date Encaissement"
            ))
            ->add('note', TextareaType::class, ["required" => false, 'label' => 'Notes'])
            // ----------------select  Essais------------------------------------------------

            ->add('essai', EntityType::class, array(

                'class' => Essais::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.nom', 'ASC');
                },
                'label' => 'Protocole',
                'choice_label' => 'nom',
                'required' => false,

                //--- ajout de la class pour activer select2 -> SELECT avec recherche d'un patient  
                'attr' => ["class" => "js-select2"],
            ))
            ->add('responsable', TextType::class, ["required" => false, 'label' => 'Responsable facture'])
// ----------------Bouton------------------------------------------------

            ->add('envoyer', SubmitType::class, array(
                'label' => "Enregistrer",
            ));
    }
// ---------------Fin-Bouton------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Facture::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_facture';
    }


}
