<?php

namespace App\Form;
//marie laure
use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilType extends AbstractType
{
    public function profilForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, ['label' => 'Pseudo :', 'required' =>true])
            ->add('prenom', TextType::class, ['label' => 'Prénom :', 'required' =>true])
            ->add('nom', TextType::class, ['label' => 'Nom :', 'required' =>true])
            ->add('telephone', TelType::class, ['label' => 'Téléphone :', 'required' =>false])
            ->add('mail', EmailType::class, ['label' => 'Email :', 'required' =>true])
            ->add('motPasse', PasswordType::class, ['label' => 'Mot de passe :', 'required' =>true])
            ->add('motPasse', PasswordType::class, ['label' => 'Confirmation :', 'required' =>true])
            ->add('campus', TextType::class, [
                'label' => 'Campus', 'required' =>true
            ], ChoiceType::class, ['choices' => [
                'NANTES' => 'Nantes',
                'RENNES' => 'Rennes',
                'NIORT' => 'Niort',
        ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}

