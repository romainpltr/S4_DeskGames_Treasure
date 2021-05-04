<?php
namespace App\Form;
 
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use App\Form\EventListener\ReCaptchaValidationListener;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

 
class UserType extends AbstractType
{
    private $reCaptcha;
    public function __construct(ReCaptcha $reCaptcha)
    {
        $this->reCaptcha = $reCaptcha;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('ProfilPicture', FileType::class,[
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('username')
            ->add('email')
            ->add('birthday', BirthdayType::class, [
                'placeholder' => [
                    'day' => 'Jour', 'month' => 'Mois', 'year' => 'Année'
                ]
            ])
            ->add('password', RepeatedType::class, [
                // instead of being set onto the object directly,
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
                // this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comprendre au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    
                ],
                /*'error_mapping' => [
                    'password' => 'Les 2 mots de passe sont différents',
                ],*/
            ])
            ->add('captcha', ReCaptchaType::class, [
                'type' => 'checkbox' // (invisible, checkbox)
            ])
            ->add('termsAccepted', CheckboxType::class, array(
                'mapped' => false,
                'constraints' => new IsTrue(),
            ))
            ->addEventSubscriber(new ReCaptchaValidationListener($this->reCaptcha));
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}