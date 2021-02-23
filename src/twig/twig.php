<?php
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CustomTwig extends Environment {

    ///////////////////////////////////////////////////////////////////////////
    public function view(string $template, array $vars = []): void {
        $html  = parent::render('/layout/header.twig');
        $html .= parent::render($template . '.twig', $vars);
        $html .= parent::render('/layout/footer.twig');

        print($html);
    }
}

$loader = new FilesystemLoader(TEMPLATES_PATH);
$twig   = new CustomTwig($loader);