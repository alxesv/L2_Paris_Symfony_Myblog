<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Tag;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;


class PostType extends AbstractType
{
    /**
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('summary')
            ->add('content')
            ->add('publicated_at', DateTimeType::class, [
                'data' => new DateTime(timezone: new \DateTimeZone('Europe/Paris')),
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date ne peut pas être antérieure à aujourd\'hui.',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPG, PNG, GIF, etc.)',
                'mapped' => true,
                'required'=>false,
            ])
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
