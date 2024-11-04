<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status')
            ->add('booking_date', null, [
                'widget' => 'single_text',
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'choices' => [
                    'Cash Payment' => 'cash',
                    'Payment Card' => 'card',

                ]
            ])
            ->add('amount')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
