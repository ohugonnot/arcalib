<?php

namespace AppBundle\Form;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\User;
use AppBundle\Services\RolesHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class UserTypeAdmin extends AbstractType
{

    /**
     * @var RolesHelper
     */
    private $roles;

    /**
     * @param RolesHelper $roles Array or roles.
     */
    public function __construct(RolesHelper $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);

        $builder->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array('label' => 'form.password'),
            'second_options' => array('label' => 'form.password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
            "required" => false
        ))
            ->add('roles', ChoiceType::class, array(
                'choices' => $this->roles->getRoles(),
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ))
            ->add('rulesProtocole', ChoiceType::class, array(
                'choices' => User::RULES_PROTOCOLE,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ))
            ->add('essais', EntityType::class, array(
                'class' => Essais::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.nom', 'ASC');
                },
                'choice_label' => 'nom',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
            ))
            ->add('medecin', EntityType::class, array(
                'class' => Medecin::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.nom', 'ASC');
                },
                'choice_label' => function (Medecin $medecin = null) {
                    return $medecin ? $medecin->getNomPrenom() : '';
                },
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ))
            ->add("enabled", null, ["label" => "Compte Activé"])
            ->remove("current_password");
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    // For Symfony 2.x

    public function getBlockPrefix()
    {
        return 'app_form_profile';
    }

}