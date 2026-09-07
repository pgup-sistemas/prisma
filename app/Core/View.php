<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public function render(string $view, array $data = [], ?string $layout = 'main'): string
    {
        $content = $this->renderFile($view, $data);

        if ($layout === null) {
            return $content;
        }

        $data['content'] = $content;

        return $this->renderFile('layouts/' . $layout, $data);
    }

    private function renderFile(string $view, array $data): string
    {
        $path = ROOT . '/app/Views/' . $view . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;

        return (string) ob_get_clean();
    }
}
