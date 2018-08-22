<?php 
class Paws extends \yii\BaseYii
{

}
spl_autoload_register([Paws::class, 'autoload'], true, true);