<?php


namespace App\Form;


use App\Entity\Comment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchCommentFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, ['attr' => ['placeholder' => 'Search for a content'], 'label' => false, 'required' => false])
            ->add('approved',ChoiceType::class,[
                'choices' => [
                    "Waiting" => NULL,
                    "Approved" => 1,
                    "Refused" => 0
                ],
                'multiple'=> true,
                'expanded'=> true,
                'label' => 'Statut : ',
                'mapped' => true,
                'data' => [NULL]
            ])
            ->add('Search', SubmitType::class )
            ->setMethod('GET')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);

    }
}
