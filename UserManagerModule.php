<?php

namespace backoffice\modules\usermanager;

use Yii;

/**
 * user manager module definition class
 */
class UserManagerModule extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backoffice\modules\usermanager\controllers';
    public $defaultRoute = 'user/index';
    public $name = 'User Manager';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Yii::configure($this, require __DIR__ . '/config/navigation.php');
    }
}
