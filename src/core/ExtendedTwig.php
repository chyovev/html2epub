<?php
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class ExtendedTwig extends Environment {

    ///////////////////////////////////////////////////////////////////////////
    // setting global variables used in all templates
    public function __construct(LoaderInterface $loader, $options = []) {
        parent::__construct($loader, $options);
        
        $this->addGlobals([
            'root'        => ROOT,
            'meta_title'  => META_TITLE,
            'meta_suffix' => META_SUFFIX,
        ]);
    }

    ///////////////////////////////////////////////////////////////////////////
    // load full page using header, footer and content template
    // and print it on screen
    public function view(string $template, array $viewVars = []): void {
        $html = $this->renderFullPage($template, $viewVars);
        print($html);
        exit;
    }

    ///////////////////////////////////////////////////////////////////////////
    // load full page using header, footer and content template,
    // but return it as a string instead of printing it
    public function renderFullPage(string $template, array $viewVars = []): string {
        // flash messages are global and as such
        // are always passed to templates by default
        $global = ['flash' => FlashMessage::getFlashMessage()];

        // merge global with user-passed vars
        // and pass the result to all templates
        $vars   = array_merge($global, $viewVars);

        $html  = parent::render('/layout/header.twig', $vars);
        $html .= parent::render($template . '.twig', $vars);
        $html .= parent::render('/layout/footer.twig', $vars);

        return $html;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function renderJSONContent($array): void {
        if ( ! is_array($array)) {
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
