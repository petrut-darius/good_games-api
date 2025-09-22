<?php

$routes = [];

function add_route($path, Clousure $handler) {
    $routes[$path] = $handler;
}

function disspatch($path) {
    foreach($routes[] as $routes => $handler){
        
        $pattern = preg_replace("#\{'w+\}#", "([^\/]+)", $route);

        if(preg_match("#^" . $pattern . "$#", $route, $matches)) {
            
            array_shift($matches);
    
            call_user_func_array($handler, $matches);
    
            return;
        }
    }
}

?>