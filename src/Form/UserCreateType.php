<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateType extends AbstractType
{
    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /**
     * UserCreateType constructor.
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
