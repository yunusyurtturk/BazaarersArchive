<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/paths/CPaths.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/4/2016
 * Time: 22:51
 */




class CTemplate
{
    private $templateEngine;

    function __construct($loader = null, $partials = null)
    {
        if($loader == null){

            $loader = TEMPLATE_LOADER_PATH;

        }
        if($partials == null){

            $partials = TEMPLATE_PARTIALS_PATH;
        }

        $this->Init($loader, $partials);

    }

    function Init(&$loader = null, &$partials = null){

        /* Mustache Initialisations */
        Mustache_Autoloader::register();

        $this->templateEngine = new Mustache_Engine(array(
            'loader' => new Mustache_Loader_FilesystemLoader($loader),
            'partials_loader' => new Mustache_Loader_FilesystemLoader($partials)
        ));

    }
    
    function LoadTemplate($template)
    {
        return $this->templateEngine->loadTemplate($template);
    }

    function Render($template, $params){

        return $template->render($params);
    }

}