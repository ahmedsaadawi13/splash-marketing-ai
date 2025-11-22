<?php
// FILE: /app/core/View.php

class View {
    private $layout = 'layouts/main';
    private $viewPath = __DIR__ . '/../views/';

    public function render($view, $data = [], $layout = null) {
        if ($layout !== null) {
            $this->layout = $layout;
        }

        extract($data);

        ob_start();
        $viewFile = $this->viewPath . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: $viewFile");
        }

        include $viewFile;
        $content = ob_get_clean();

        if ($this->layout) {
            $layoutFile = $this->viewPath . $this->layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    public function renderPartial($partial, $data = []) {
        extract($data);
        $partialFile = $this->viewPath . $partial . '.php';

        if (!file_exists($partialFile)) {
            throw new Exception("Partial file not found: $partialFile");
        }

        include $partialFile;
    }

    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function e($string) {
        return self::escape($string);
    }
}
