<?php
namespace paws\web;

use Paws;
use yii\web\Response;

class Controller extends \yii\web\Controller
{
    public function render($template, $variables = []): Response
    {
        $response = Paws::$app->getResponse();
        $response->data = parent::render($template, $variables);
        $response->format = Response::FORMAT_RAW;
        return $response;
    }
}