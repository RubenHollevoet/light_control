<?php

namespace App\Form;

use App\Entity\Call;
use App\Entity\Device;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CallType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tagOptions = [];
        $tags = $this->em->getRepository(Tag::class)->findAll();
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $tagOptions[$tag->getName()] = 't'.$tag->getId();
        }

        $deviceOptions = [];
        $devices = $this->em->getRepository(Device::class)->findAll();
        /** @var Device $device */
        foreach ($devices as $device) {
            $deviceOptions[$device->getName()] = $device->getId();
        }

        $devicesAndTags = [];
        $devicesAndTags['Tags'] = $tagOptions;
        $devicesAndTags['Devices'] = $deviceOptions;

        $builder
            ->add('api', ChoiceType::class, [
                'label' => false,
                'mapped' => false,
                'choices'  => [
                    'Yeelight' => 'yeelight',
                ],
            ])
            ->add('content', TextType::class, [
                'label' => false,
            ])
            ->add('start', null, [
                'label' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($devicesAndTags) {
            /** @var Call $call */
            $call = $event->getData();
            $form = $event->getForm();

            $deviceTagData = null;
            if ($call && null !== $call->getId()) {
                $deviceTagData = $call->getTag() ? 't'.$call->getTag()->getId() : $call->getDevice()->getId();
            }

            $form->add('deviceTag', ChoiceType::class, [
                'mapped' => false,
                'label' => false,
                'choices' => $devicesAndTags,
                'data' => $deviceTagData,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Call::class,
        ]);
    }
}
