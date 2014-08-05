<?php
$this->breadcrumbs=array(
    Yii::t('auth', 'Users')=>array('index'),
    $model->email
);

if(Yii::app()->user->checkAccess('userManager'))
{
    $this->menu[] = array(
        'label' => Yii::t('ica_auth', 'List users'),
        'url' => '/user/index',
    );
    $this->menu[] = array(
        'label' => Yii::t('ica_auth', 'Create new user'),
        'url' => '/user/create',
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

    <h1><?php echo Yii::t('auth', 'Update user') ?></h1>

<?php
echo $this->renderPartial('_form', array('model'=>$model));



