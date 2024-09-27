<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use http\Client\Curl\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class SortieType extends AbstractType
{
    // Retire le constructeur, pas nécessaire ici
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes)',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu',
                'placeholder' => 'Sélectionnez une ville d\'abord', // Indication pour l'utilisateur
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false
            ])
            ->add('longitude', EntityType::class, [
                'class'=>Lieu::class,
                'choice_label' => 'Longitude',
                'mapped'=>false,
                'attr' => ['readonly' => true], // Empêcher la modification manuelle
            ])
            ->add('latitude', EntityType::class, [
                'class'=>Lieu::class,
                'choice_label' => 'Latitude',
                'mapped'=>false,
                'attr' => ['readonly' => true],
                // Empêcher la modification manuelle
            ])
            ->add('Enregistrer', SubmitType::class, ['label' => 'Enregistrer',
                'attr' => ['style' => 'display:none;'] ])
            ->add('Publier', SubmitType::class, ['label' => 'Publier',
                'attr' => ['style' => 'display:none;'] ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'csrf_protection' => true,  // C'est déjà activé par défaut
            'csrf_field_name' => '_token',  // Le nom du champ de token
            'csrf_token_id' => 'sortie',  // L'identifiant du token CSRF
        ]);
    }

}
