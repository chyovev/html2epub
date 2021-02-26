<?php
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class CustomTwig extends Environment {

    ///////////////////////////////////////////////////////////////////////////
    // setting global variables used in all templates
    public function __construct(LoaderInterface $loader, $options = []) {
        parent::__construct($loader, $options);
        
        $this->addGlobal('root',  ROOT);
    }

    ///////////////////////////////////////////////////////////////////////////
    // generating a full page with header, footer and content template
    public function view(string $template, array $viewVars = []): void {
        // set the flash message if any
        $this->addGlobal('flash', FlashMessage::getFlashMessage());
        
        $html  = parent::render('/layout/header.twig');
        $html .= parent::render($template . '.twig', $viewVars);
        $html .= parent::render('/layout/footer.twig');

        print($html);
        exit;
    }

    ///////////////////////////////////////////////////////////////////////////
    // when propel validation fails, all errors are returned as objects
    // convert them to an array for easier access in the templates
    function addGlobalValidationFailures($failures): void {
        $errors = [];

        foreach ($failures as $item) {
            $field            = $item->getPropertyPath();
            $message          = $item->getMessage();
            $errors[$field][] = $message;
        }

        $this->addGlobal('errors', $errors);
    }
}

$loader = new FilesystemLoader(TEMPLATES_PATH);
$twig   = new CustomTwig($loader);

// registering the Url abstract class as custom_url function in twig
$urlFunction = new TwigFunction('custom_url', function($method, ...$args) {
    return forward_static_call_array(['Url', $method], $args);    
});
$twig->addFunction($urlFunction);
