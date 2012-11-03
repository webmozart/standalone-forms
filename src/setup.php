<?php

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\Templating\TemplatingExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Validator\Validation;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\TranslatorHelper;

class SimpleTemplateNameParser implements TemplateNameParserInterface
{
    private $root;

    public function __construct($root)
    {
        $this->root = $root;
    }

    public function parse($name)
    {
        if (false !== strpos($name, ':')) {
            $path = str_replace(':', '/', $name);
        } else {
            $path = $this->root . '/' . $name;
        }

        return new TemplateReference($path, 'php');
    }
}

// Overwrite this with your own secret
define('CSRF_SECRET', 'c2ioeEU1n48QF2WsHGWd2HmiuUUT6dxr');

define('VENDOR_DIR', realpath(__DIR__ . '/../vendor'));
define('VENDOR_FORM_DIR', VENDOR_DIR . '/symfony/form/Symfony/Component/Form');
define('VENDOR_VALIDATOR_DIR', VENDOR_DIR . '/symfony/form/Symfony/Component/Validator');
define('VENDOR_FRAMEWORK_BUNDLE_DIR', VENDOR_DIR . '/symfony/framework-bundle/Symfony/Bundle/FrameworkBundle');
define('VIEWS_DIR', realpath(__DIR__ . '/../views'));

// Set up the CSRF provider
$csrfProvider = new DefaultCsrfProvider(CSRF_SECRET);

// Set up the Validator component
$validator = Validation::createValidator();

// Set up the Translation component
$translator = new Translator('en');
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource('xlf', VENDOR_FORM_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');
$translator->addResource('xlf', VENDOR_VALIDATOR_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');

// Set up the Templating component
$engine = new PhpEngine(new SimpleTemplateNameParser(VIEWS_DIR), new FilesystemLoader(array()));
$engine->addHelpers(array(new TranslatorHelper($translator)));

// Set up the Form component
$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfExtension($csrfProvider))
    ->addExtension(new TemplatingExtension($engine, null, array(
        // Will hopefully not be necessary anymore in 2.2
            VENDOR_FRAMEWORK_BUNDLE_DIR . '/Resources/views/Form',
    )))
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();
