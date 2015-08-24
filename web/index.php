<?php

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/setup.php';

// Create our first form!
$form = $formFactory->createBuilder()
    ->add('firstName', 'text', array(
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 4)),
        ),
    ))
    ->add('lastName', 'text', array(
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 4)),
        ),
    ))
    ->add('gender', 'choice', array(
        'choices' => array('m' => 'Male', 'f' => 'Female'),
    ))
    ->add('newsletter', 'checkbox', array(
        'required' => false,
    ))
    ->add('email', 'email', array(
        'label' => 'Email',
        'constraints' => array(
            new NotBlank(),
            new Length(array('max' => 255)),
            new Email(),
        ),
    ))
    ->add('reEmail', 'email', array(
        'label' => 'Re-Email',
        'constraints' => array(
            new NotBlank(),
            new Length(array('max' => 255)),
            new Email(),
        ),
    ))
    ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
        $form = $event->getForm();
        $formData = $form->getData();
        if ($formData['email'] != $formData['reEmail']) {
            $event->getForm()->get('email')->addError(new FormError('Email address is not the same like ReEmail address'));
            $event->getForm()->get('reEmail')->addError(new FormError('ReEmail address is not the same like Email address'));
        }
    })
    ->getForm();

if (isset($_POST[$form->getName()])) {
    $form->submit($_POST[$form->getName()]);

    if ($form->isValid()) {
        var_dump('VALID', $form->getData());
        die;
    }
}

echo $twig->render('index.html.twig', array(
    'form' => $form->createView(),
));
