<?php

namespace Opencart\System\Library\BluePayment\Helper;

use Opencart\System\Engine\Registry;

final class LibraryLoader
{
    public static function loadBluepaymentLibrary(Registry $registry, string $route, &...$args): object
    {
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);
        $key = 'library_' . str_replace('/', '_', $route);

        if ($registry->has($key)) {
            return $registry->get($key);
        }

        $load = $registry->get('load');

        if (method_exists($load, 'library')) {
            $object = $load->library($route, ...$args);

            if (!$registry->has($key)) {
                $registry->set($key, $object);
            }

            return $object;
        }

        $class = 'Opencart\\System\\Library\\' . str_replace(['_', '/'], ['', '\\'], ucwords($route, '_/'));

        if (!class_exists($class)) {
            throw new \Exception('Error: Could not load library ' . $route . '!');
        }

        $object = new $class(...$args);
        $registry->set($key, $object);

        return $object;
    }
}
