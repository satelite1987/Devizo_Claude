<?php
/**
 * DEVIZO v2.0 - URL Router
 *
 * Handles URL routing, clean URLs, and request dispatching
 */

if (!defined('DEVIZO_APP')) {
    die('Direct access not permitted');
}

class Router {

    private static $routes = [];
    private static $currentRoute = null;

    /**
     * Add a GET route
     */
    public static function get($pattern, $callback) {
        self::addRoute('GET', $pattern, $callback);
    }

    /**
     * Add a POST route
     */
    public static function post($pattern, $callback) {
        self::addRoute('POST', $pattern, $callback);
    }

    /**
     * Add route for any method
     */
    public static function any($pattern, $callback) {
        self::addRoute('*', $pattern, $callback);
    }

    /**
     * Add a route
     */
    private static function addRoute($method, $pattern, $callback) {
        self::$routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }

    /**
     * Dispatch the request
     */
    public static function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Remove query string
        $requestUri = strtok($requestUri, '?');

        // Remove base path from URI
        $basePath = parse_url(SITE_URL, PHP_URL_PATH) ?? '';
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        // Ensure leading slash
        $requestUri = '/' . ltrim($requestUri, '/');

        // Try to match route
        foreach (self::$routes as $route) {
            if ($route['method'] !== '*' && $route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = self::convertPattern($route['pattern']);

            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                self::$currentRoute = $route;

                // Execute callback
                return self::executeCallback($route['callback'], $matches);
            }
        }

        // No route matched - 404
        self::notFound();
    }

    /**
     * Convert route pattern to regex
     */
    private static function convertPattern($pattern) {
        // Convert {id}, {slug}, etc to regex capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $pattern);

        // Add start and end anchors
        return '#^' . $pattern . '$#';
    }

    /**
     * Execute route callback
     */
    private static function executeCallback($callback, $params) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_string($callback)) {
            // Controller@method format
            if (strpos($callback, '@') !== false) {
                list($controller, $method) = explode('@', $callback);

                $controllerClass = $controller;
                if (!class_exists($controllerClass)) {
                    throw new Exception("Controller '{$controllerClass}' not found.");
                }

                $controllerInstance = new $controllerClass();

                if (!method_exists($controllerInstance, $method)) {
                    throw new Exception("Method '{$method}' not found in controller '{$controllerClass}'.");
                }

                return call_user_func_array([$controllerInstance, $method], $params);
            }

            // File path
            if (file_exists($callback)) {
                extract($params);
                require $callback;
                return;
            }
        }

        throw new Exception('Invalid route callback.');
    }

    /**
     * Get current route
     */
    public static function getCurrentRoute() {
        return self::$currentRoute;
    }

    /**
     * Check if current route matches pattern
     */
    public static function is($pattern) {
        if (!self::$currentRoute) {
            return false;
        }
        return self::$currentRoute['pattern'] === $pattern;
    }

    /**
     * 404 Not Found
     */
    public static function notFound() {
        http_response_code(404);
        if (file_exists(APP_ROOT . '/views/404.php')) {
            require APP_ROOT . '/views/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The page you are looking for could not be found.</p>';
        }
        exit;
    }

    /**
     * Redirect to URL
     */
    public static function redirect($url, $statusCode = 302) {
        redirect($url, $statusCode);
    }

    /**
     * Redirect to named route (simple implementation)
     */
    public static function redirectToRoute($pattern, $params = []) {
        $url = $pattern;

        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }

        self::redirect(url($url));
    }

    /**
     * Generate URL from pattern and parameters
     */
    public static function url($pattern, $params = []) {
        $url = $pattern;

        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }

        return url($url);
    }
}
