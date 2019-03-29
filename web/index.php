<?php

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/setup.php';

// Create our first form!
$form = $formFactory->createBuilder()
    ->add('firstName', TextType::class, [
        'constraints' => [
            new NotBlank(),
            new Length(['min' => 4]),
        ],
    ])
    ->add('lastName', TextType::class, [
        'constraints' => [
            new NotBlank(),
            new Length(['min' => 4]),
        ],
    ])
    ->add('gender', ChoiceType::class, [
        'choices' => [
            'Male' => 'm', 
            'Female' => 'f'
        ],
    ])
    ->add('newsletter', CheckboxType::class, [
        'required' => false,
    ])
    ->getForm();

var_dump($_POST);

if (isset($_POST[$form->getName()])) {
    $form->submit($_POST[$form->getName()]);

    if ($form->isValid()) {
        var_dump('VALID', $form->getData());
        die;
    }
}

echo $twig->render('index.html.twig', [
    'form' => $form->createView(),
]);
