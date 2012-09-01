<?php

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;
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
use Symfony\Bundle\FrameworkBundle\Templating\Helper\TranslatorHelper;

require __DIR__ . '/../vendor/autoload.php';

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
$csrfSecret = 'c2ioeEU1n48QF2WsHGWd2HmiuUUT6dxr';

// Set up requirements - hopefully we can facilitate this more in 2.2
$validator = Validation::createValidator();
$translator = new Translator('en');
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource('xlf', realpath(__DIR__ . '/../vendor/symfony/form/Symfony/Component/Form/Resources/translations/validators.en.xlf'), 'en', 'validators');
$translator->addResource('xlf', realpath(__DIR__ . '/../vendor/symfony/validator/Symfony/Component/Validator/Resources/translations/validators.en.xlf'), 'en', 'validators');
$engine = new PhpEngine(new SimpleTemplateNameParser(realpath(__DIR__ . '/../views')), new FilesystemLoader(array()));
$engine->addHelpers(array(new TranslatorHelper($translator)));

// Set up the form factory with all desired extensions
$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfExtension(new DefaultCsrfProvider($csrfSecret)))
    ->addExtension(new TemplatingExtension($engine, null, array(
        // Will hopefully not be necessary anymore in 2.2
        realpath(__DIR__ . '/../vendor/symfony/framework-bundle/Symfony/Bundle/FrameworkBundle/Resources/views/Form'),
    )))
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();

// Create our first form!
$form = $formFactory->createBuilder()
    ->add('firstName', 'text', array(
        'constraints' => array(
            new NotBlank(),
            new MinLength(4),
        ),
    ))
    ->add('lastName', 'text', array(
        'constraints' => array(
            new NotBlank(),
            new MinLength(4),
        ),
    ))
    ->add('gender', 'choice', array(
        'choices' => array('m' => 'Male', 'f' => 'Female'),
    ))
    ->add('newsletter', 'checkbox', array(
        'required' => false,
    ))
    ->getForm();

if (isset($_POST[$form->getName()])) {
    $form->bind($_POST[$form->getName()]);

    if ($form->isValid()) {
        var_dump('VALID', $form->getData());
        die;
    }
}

echo $engine->render('index.html.php', array(
    'form' => $form->createView(),
));
