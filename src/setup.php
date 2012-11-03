<?php

use Symfony\Component\Validator\Validation;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;

// Overwrite this with your own secret
$csrfSecret = 'c2ioeEU1n48QF2WsHGWd2HmiuUUT6dxr';

// Set up requirements - hopefully we can facilitate this more in 2.2
$csrfProvider = new DefaultCsrfProvider($csrfSecret);
$validator = Validation::createValidator();
$translator = new Translator('en');
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource('xlf', realpath(__DIR__ . '/../vendor/symfony/form/Symfony/Component/Form/Resources/translations/validators.en.xlf'), 'en', 'validators');
$translator->addResource('xlf', realpath(__DIR__ . '/../vendor/symfony/validator/Symfony/Component/Validator/Resources/translations/validators.en.xlf'), 'en', 'validators');
$loader = new Twig_Loader_Filesystem(array(
    realpath(__DIR__ . '/../views'),
    realpath(__DIR__ . '/../vendor/symfony/twig-bridge/Symfony/Bridge/Twig/Resources/views/Form'),
));
$twigFormEngine = new TwigRendererEngine(array('form_div_layout.html.twig'));
$twig = new Twig_Environment($loader, array(
    'cache' => realpath(__DIR__ . '/../cache'),
));
$twig->addExtension(new TranslationExtension($translator));
$twig->addExtension(new FormExtension(new TwigRenderer($twigFormEngine, $csrfProvider)));
$twigFormEngine->setEnvironment($twig);

// Set up the form factory with all desired extensions
$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfExtension($csrfProvider))
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();
