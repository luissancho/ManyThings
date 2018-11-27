<?php

namespace ManyThings\Core;

class Loader
{
    protected $namespaces = [];

    public function register()
    {
        // Register loader with SPL autoloader stack
        spl_autoload_register([$this, 'autoload']);
    }

    public function addNamespaces(array $namespaces, $prepend = false)
    {
        foreach ($namespaces as $namespace => $path) {
            // Normalize namespace and path
            $namespace = trim($namespace, '\\') . '\\';
            $path = rtrim($path, '/') . '/';

            // Initialize namespace array
            if (empty($this->namespaces[$namespace])) {
                $this->namespaces[$namespace] = [];
            }

            // Retain path for namespace
            if ($prepend) {
                array_unshift($this->namespaces[$namespace], $path);
            } else {
                array_push($this->namespaces[$namespace], $path);
            }
        }

        return $this;
    }

    public function autoload($class)
    {
        // Current namespace
        $namespace = $class;

        // Work backwards through the namespace names
        while (false !== $pos = strrpos($namespace, '\\')) {

            // Retain the trailing namespace separator in the namespace
            $namespace = substr($class, 0, $pos + 1);
            // The rest is the relative class name
            $className = substr($class, $pos + 1);

            // Look through the paths for this namespace
            if (!empty($this->namespaces[$namespace])) {
                foreach ($this->namespaces[$namespace] as $path) {

                    // Replace namespace with path
                    $fileName = $path . str_replace('\\', '/', $className) . '.class.php';

                    // If the mapped file exists, require it
                    if (file_exists($fileName)) {
                        require $fileName;

                        return true;
                    }
                }
            }

            // Remove trailing namespace separator for the next iteration
            $namespace = rtrim($namespace, '\\');
        }

        return false;
    }

    public function getClassName($class)
    {
        foreach ($this->namespaces as $namespace => $path) {
            $className = $namespace . $class;
            if (class_exists($className)) {
                return $className;
            }
        }
    }
}
