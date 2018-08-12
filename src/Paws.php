<?php 
class Paws extends Yii
{

}
spl_autoload_register([Paws::class, 'autoload'], true, true);