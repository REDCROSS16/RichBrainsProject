<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\ArrayToPartsTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new Length([
                        'min'        => 2,
                        'minMessage' => 'Your firstname is too short'
                    ]),
                    new NotBlank([
                        'message' => 'This field can not be blank'
                    ])
                ]
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new Length([
                        'min'        => 2,
                        'minMessage' => 'Your firstname is too short'
                    ]),
                    new NotBlank([
                        'message' => 'This field can not be blank'
                    ])
                ]
            ])
            ->add('email', EmailType::class,
            [
                'constraints' =>[
                    new Email([
                        'message'=>'This is not the corect email format'
                    ]),
                    new NotBlank([
                        'message' => 'This field can not be blank'
                    ])
                ],
            ]
            )
            ->add('phoneNumber', null,
                [
                    'constraints' => [
                    new NotBlank([
                        'message' => 'This field can not be blank'
                    ]),
                        new Regex('/(\+)[0-9]{7,}$/','Enter a phone like +ххххххх')
                    ]
                ]
            )
            ->add('password', PasswordType::class,
            [
                'constraints' => [
                    new NotBlank([
                        'message' => "Enter password"
                    ])
                ]
            ]
            )
            ->add('roles', null,
            [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
