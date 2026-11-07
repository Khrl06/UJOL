<?php
/**
 * SmartExam AI - Base View
 * Engine render template view dengan dukungan layout modular dan partial components.
 */

namespace App\Core;

class View
{
    /**
     * Render view dengan layout tertentu
     *
     * @param string $viewPath Path view relatif terhadap direktori app/Views (misal: 'admin/dashboard')
     * @param array $data Data variabel yang dilewatkan ke view
     * @param string|null $layout Nama layout ('main', 'auth', atau null jika tanpa layout)
     */
    public static function render(string $viewPath, array $data = [], ?string $layout = 'main'): void
    {
        // Ekstrak variabel agar langsung dapat dipanggil di view
        extract($data);

        // Path lengkap file view
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View file tidak ditemukan: <code>{$viewFile}</code>");
        }

        // Tangkap output view ke buffer jika menggunakan layout
        if ($layout !== null) {
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            // Path file layout
            $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                // Fallback jika layout tidak ditemukan, cetak langsung kontennya
                echo $content;
            }
        } else {
            // Render langsung tanpa layout
            require $viewFile;
        }
    }

    /**
     * Render partial component (misal: header, navbar, sidebar, footer)
     *
     * @param string $partialName Nama file partial dalam app/Views/partials/
     * @param array $data Data tambahan untuk partial
     */
    public static function partial(string $partialName, array $data = []): void
    {
        extract($data);
        $partialFile = APP_PATH . '/Views/partials/' . $partialName . '.php';

        if (file_exists($partialFile)) {
            require $partialFile;
        } else {
            echo "<!-- Partial {$partialName} not found -->";
        }
    }
}
