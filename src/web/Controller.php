<?php
namespace paws\web;

use yii\web\Response;
use Paws;
use paws\web\Application;

class Controller extends \yii\web\Controller
{
    // public function render($template, $variables = []): Response
    // {
    //     $response = Paws::$app->getResponse();
    //     if ($response instanceof Response)
    //     {
    //         $response->data = parent::render($template, $variables);
    //         $response->format = Response::FORMAT_RAW;
    //         return $response;
    //     }
    // }
}