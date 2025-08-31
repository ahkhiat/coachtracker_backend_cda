<?php

namespace App\Form;

use App\Entity\Goal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class GoalType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('minuteGoal', IntegerType::class, [
                'label' => 'Minute du but',
                'attr' => ['min' => 0, 'max' => 120]
            ])
             ->add('selectedPlayer', ChoiceType::class, [
                'mapped' => false,
                'label' => 'Joueur',
                'choices' => $options['players'],
                'choice_label' => function($player) {
                    return $player->getUser() ? $player->getUser()->getFirstname() . ' ' . $player->getUser()->getLastname() : 'Joueur fantôme';
                },
                'choice_value' => function($player) {
                    return $player ? $player->getId() : '';
                },
                'placeholder' => 'Sélectionner un joueur',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Goal::class,
            'players' => [],
        ]);
    }
}


