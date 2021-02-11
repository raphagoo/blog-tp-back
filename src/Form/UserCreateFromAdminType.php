<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateFromAdminType extends AbstractType
{
    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /**
     * UserCreateFromAdminType constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $encoder = $this->encoder;
        $builder
            ->add(
                'roles', ChoiceType::class, [
                    'choices' => ['ROLE_ADMIN' => 'ROLE_ADMIN', 'ROLE_USER' => 'ROLE_USER', 'ROLE_CUSTOMER' => 'ROLE_CUSTOMER'],
                    'expanded' => true,
                    'multiple' => true,
                ]
            )
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) use($encoder){
                /** @var User */
                $user = $event->getData();
                if($user) {
                    $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
                }
            })
        ;
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return UserType::class;
    }
}
