<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TimeSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('morning', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'string',
                'required' => false,
                'label' => 'Morning Slot',
            ])
            ->add('afternoon', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'string',
                'required' => false,
                'label' => 'Afternoon Slot',
            ])
            ->add('unavailable', CheckboxType::class, [
                'required' => false,
                'label' => 'Unavailable',
            ]);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData(); // Données soumises par l'utilisateur
            $form = $event->getForm();

            // Si morning et afternoon ne sont pas définis, coche unavailable
            if (empty($data['morning']) || empty($data['afternoon'])) {
                $data['unavailable'] = true; // On coche la case unavailable
            }

            $event->setData($data); // On met à jour les données
        });
    }
}