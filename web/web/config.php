<?php
ini_set ( 'display_errors', 1 );
session_start();

$defaultLocale = 'en_US';

$locale = false; // initilize language parameter

if (isset($_GET["locale"])) // check if getting locale value in query string
{
    $locale = $_GET["locale"]; // add selected language in variable

}else{
    if(isset($_COOKIE)){

        if(isset($_COOKIE['locale'])){

            $locale = $_COOKIE['locale']; // set English US as default language
        }
    }

}

if(false === $locale){

    $locale = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
}

$results =putenv("LANGUAGE=$locale");

$results = setlocale(LC_ALL, $locale);
if (!$results) {
    $locale = $defaultLocale;
    $results =putenv("LANGUAGE=$locale");
    $results = setlocale(LC_ALL, $locale);
}else{
    if(isset($_GET["locale"])){

        if(setcookie('locale', $_GET["locale"], time() + (93312000), "/")); // 93312000 = around 3 years
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;

    }
}


$domain = "bazaarers";
$results = bindtextdomain($domain, "/var/www/html/BazaarersDomain/locale");



$results = bind_textdomain_codeset($domain, "UTF-8");



$results = textdomain($domain);


