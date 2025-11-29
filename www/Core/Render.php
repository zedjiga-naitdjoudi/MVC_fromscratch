<?php

namespace App\Core;

class Render
{
    private string $pathView;
    private string $pathTemplate;
    private array $data = [];

    public function __construct(string $view = "home", string $template = "frontoffice")
    {
        $this->setView($view);
        $this->setTemplate($template);
    }

    public function setView(string $view): void
    {
        $this->pathView = __DIR__ . "/../Views/{$view}.php";
    }

    public function setTemplate(string $template): void
    {
        $this->pathTemplate = __DIR__ . "/../Views/Templates/{$template}.php";
    }

    private function check(): bool
    {
        return file_exists($this->pathTemplate) && file_exists($this->pathView);
    }

    public function assign(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function render(): void
    {
        if ($this->check()) {
            extract($this->data);
            include $this->pathTemplate;
        } else {
            die("Problème avec le template ou la vue");
        }
    }


    
}
