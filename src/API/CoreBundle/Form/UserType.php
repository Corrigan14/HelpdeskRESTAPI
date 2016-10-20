<?php

namespace API\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username')
            ->add('password')
            ->add('email');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
//        $collectionConstraint = new Collection([
//            'email' => [
//                new NotBlank(['message' => 'Email should not be blank.']),
//                new Email(['message' => 'Invalid email address.'])
//            ]
//        ]);

        $resolver->setDefaults([
//            'constraints' => $collectionConstraint,
            'data_class' => 'API\CoreBundle\Entity\User',
            'csrf_protection' => false,
        ]);
    }
}
