<?php

namespace App\Form;

use App\Entity\Accessory;
use App\Entity\Platform;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                "label" => "Nom",
            ])
            ->add('imgName', FileType::class, [
                "label" => "Image",
                "required" => false
            ])
            ->add('model', TextType::class, [
                "label" => "Modèle",
            ])
//            ->add('platform', EntityType::class, [
//                "label" => "Plateforme(s)",
//                "class" => Platform::class,
//                "choice_label" => "name"
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Accessory::class,
        ]);
    }
}
