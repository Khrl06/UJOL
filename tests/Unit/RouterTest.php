<?php
namespace Tests\Unit;

use App\Core\App;

class RouterTest extends TestCase
{
    public function testCoreRoutesAreConfigured(): void
    {
        $ref = new \ReflectionClass(App::class);
        $app = $ref->newInstanceWithoutConstructor();

        $prop = $ref->getProperty('routes');
        $prop->setAccessible(true);
        $routes = $prop->getValue($app);

        $this->assertIsArray($routes);
        
        // Rute publik utama
        $this->assertArrayHasKey('', $routes);
        $this->assertArrayHasKey('login', $routes);
        $this->assertArrayHasKey('verify-otp', $routes);
        $this->assertArrayHasKey('logout', $routes);
        $this->assertArrayHasKey('dashboard', $routes);

        // Rute modul Admin
        $this->assertArrayHasKey('admin', $routes);
        $this->assertArrayHasKey('admin/dashboard', $routes);
        $this->assertArrayHasKey('admin/settings', $routes);

        // Rute modul Guru & Siswa
        $this->assertArrayHasKey('teacher', $routes);
        $this->assertArrayHasKey('student', $routes);
    }

    public function testRouteTargetsValidControllers(): void
    {
        $ref = new \ReflectionClass(App::class);
        $app = $ref->newInstanceWithoutConstructor();

        $prop = $ref->getProperty('routes');
        $prop->setAccessible(true);
        $routes = $prop->getValue($app);

        foreach (['login', 'admin/dashboard', 'dashboard', 'admin/settings'] as $routeKey) {
            $target = $routes[$routeKey];
            $controllerClass = 'App\\Controllers\\' . $target['controller'];
            $this->assertTrue(class_exists($controllerClass), "Controller {$controllerClass} harus ada.");
            $this->assertTrue(method_exists($controllerClass, $target['method']), "Method {$target['method']} harus ada di {$controllerClass}.");
        }
    }
}
