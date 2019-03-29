<?php
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;


define('DEFAULT_FORM_THEME', 'form_div_layout.html.twig');

define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form');
define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/validator');
define('VENDOR_TWIG_BRIDGE_DIR', VENDOR_DIR . '/symfony/twig-bridge');
define('VIEWS_DIR', realpath(__DIR__ . '/../views'));

// Set up the CSRF Token Manager
// creates a Session object from the HttpFoundation component
// $session = new Session();

$csrfGenerator = new UriSafeTokenGenerator();
$csrfStorage = new NativeSessionTokenStorage();
$csrfManager = new CsrfTokenManager($csrfGenerator, $csrfStorage);

$csrfTokenManager = new CsrfTokenManager();

// Set up the Validator component
$validator = Validation::createValidator();

// Set up the Translation component
$translator = new Translator('en');
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource('xlf', VENDOR_FORM_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');
$translator->addResource('xlf', VENDOR_VALIDATOR_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');

$twig = new Environment(new FilesystemLoader([
    VIEWS_DIR,
    VENDOR_TWIG_BRIDGE_DIR.'/Resources/views/Form',
]));


$formEngine = new TwigRendererEngine([DEFAULT_FORM_THEME], $twig);

$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine, $csrfManager) {
        return new FormRenderer($formEngine, $csrfManager);
    },
]));


$twig->addExtension(new TranslationExtension($translator));
$twig->addExtension(new FormExtension());

// Set up the Form component
$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfExtension($csrfTokenManager))
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();
