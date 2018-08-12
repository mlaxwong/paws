<?php
return \yii\helpers\ArrayHelper::merge(
    require __DIR__ . DIRECTORY_SEPARATOR . 'app.php',
    require __DIR__ . DIRECTORY_SEPARATOR . 'app.test.php'
);