<?php
$this->breadcrumbs=array(
    Yii::t('auth', 'Users') =>array('index'), 
    Yii::t('auth', 'Create'), 
);

if(Yii::app()->user->checkAccess('userManager'))
{
    $this->menu[] = array(
        'label' => Yii::t('ica_auth', 'List users'),
        'url' => '/user/index',
    );
}

if(Yii::app()->user->checkAccess('authItemEditor'))
{
    $this->menu[] = array(
        'label' => Yii::t('ica_auth', 'Edit roles / permissions'),
        'url' => '/authItem',
    );
}
?>

    <h1><?php echo Yii::t('auth', 'Create user'); ?></h1>

<?php
echo $this->renderPartial('_form', array('model'=>$model));

