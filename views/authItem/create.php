<?php
$this->breadcrumbs=array(
    Yii::t('auth', 'Auth Items') => array('index'), 
    Yii::t('auth', 'Create'), 
);

?>

    <h1><?php echo Yii::t('auth', 'Create Auth Item') ?></h1>

<?php

echo $this->renderPartial('_form', array('model'=>$model));

