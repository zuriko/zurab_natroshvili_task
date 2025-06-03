<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\News;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class NewsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('shortDescription', TextType::class)
            ->add('content', null, ['attr' => ['rows' => 6]])
            ->add('insertedAt')
            ->add('categories', null, [
                'expanded' => false,
                'multiple' => true,
                'required' => true,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'News Image (JPG, PNG, GIF)',
                'mapped' => false,
                'required' => false, // For editing, can be empty
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (.jpg, .jpeg, .png, .gif)',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
