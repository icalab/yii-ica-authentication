<?php

Yii::import('application.models.*');
class ICAInitAuthCommand extends CConsoleCommand
{
    public function run($args)
    {
        ICAUser::setup();
    }
}

