<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Convocation;
use App\Enum\PresenceStatusEnum;
use App\Enum\ConvocationStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OneConvocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('player', EntityType::class, [
                'class' => Player::class,
                'choice_label' => fn(Player $p) => $p->getUser()->getFirstname() . ' ' . $p->getUser()->getLastname(),
                'placeholder' => '-- Choisir une joueuse --',
                'choices' => $options['availablePlayers'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Convocation::class,
            'availablePlayers' => []
        ]);
    }
}
