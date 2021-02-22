<?php

namespace App\Form;

use App\Entity\Device;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false
            ])
            ->add('ip', TextType::class, [
                'label' => false
            ])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices'  => [
                    [
                        'Yeelight' => [
                            Device::TYPE_YEELIGHT_BULB_COLOR_2 => Device::TYPE_YEELIGHT_BULB_COLOR_2,
                            Device::TYPE_YEELIGHT_BULB_COLOR_S1 => Device::TYPE_YEELIGHT_BULB_COLOR_S1,
                            Device::TYPE_YEELIGHT_STRIP_COLOR => Device::TYPE_YEELIGHT_STRIP_COLOR,
                            Device::TYPE_YEELIGHT_FULFILLMENT => Device::TYPE_YEELIGHT_FULFILLMENT,
                        ],
                        'Custom' => [
                            Device::TYPE_WLED => Device::TYPE_WLED,
                        ],
//                        'Other' => [
//                            Device::TYPE_ARDUINO_TODO => Device::TYPE_ARDUINO_TODO,
//                        ]
                    ]

                ],
            ])
            ->add('tags', null, [
                'label' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Device::class,
        ]);
    }
}
