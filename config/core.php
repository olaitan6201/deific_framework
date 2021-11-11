<?php

use Controllers\Crud;
use Controllers\DB;
use Providers\Response;

//Gets environment variable value from .env file
function env(String $search, ?String $default = '')
{
    $lines_array = file(".env");

    $new_str = "";

    foreach($lines_array as $line) {
        if(strpos($line, $search) !== false) {
            list(, $new_str) = explode("=", $line);
            $new_str = trim($new_str);
        }
    }

    return !empty($new_str)?$new_str:$default;
}

//Adds a view file
function view(String $name)
{
    include_once "app/views/$name.view.php";
}

//Autoload all files in a folder
function __autoload__($folder){
    foreach (glob("$folder/*.php") as $filename)
    {
        include $filename;
    }
}

//Redirect to a given url
function redirect($url)
{
    # code...
}

//JSON notation to object
function to_object($notation)
{
    return json_decode($notation);
}

//JSON notation to array
function to_array($notation)
{
    return json_decode($notation, true);
}


//Returns link to storage/public folder
function storage($path)
{
    return env('APP_URL')."/storage/public/$path";
}


//Returns link to app/assets folder
function asset($path)
{
    return env('APP_URL')."/app/assets/$path";
}

//Returns link to app/assets/bootstrap folder
function app_asset($path)
{
    return env('APP_URL')."/app/assets/bootstrap/$path";
}


/**
 * 
 * Controllers Instances
 */

//Creates a new instance for Controllers\DB::class
function db()
{
    return new DB();
}

//Creates a new instance for Controllers\Crud::class
function crud()
{
    return new Crud();
}


/**
 * 
 * Providers Instances
 */

//Creates a new instance for Providers\Response::class
function response()
{
    return new Response();
}