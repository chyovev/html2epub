<?php
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class ExtendedTwig extends Environment {

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

        $this->addGlobals($viewVars);
        
        $html  = parent::render('/layout/header.twig');
        $html .= parent::render($template . '.twig');
        $html .= parent::render('/layout/footer.twig');

        print($html);
        exit;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function renderJSONContent($array): void {
        if (!is_array($array)) {
            $array = [$array];
        }

        header('Content-Type: application/json');
        die(json_encode($array));
    }

    ///////////////////////////////////////////////////////////////////////////
    public function addGlobals(array $viewVars = []): void {
        foreach ($viewVars as $var => $value) {
            $this->addGlobal($var, $value);
        }
    }

}
