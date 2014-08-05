<?php
$this->breadcrumbs=array(
    Yii::t('auth', 'Users'), 
);

if(Yii::app()->user->checkAccess('userManager'))
{
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
    <h1><?php echo Yii::t('auth', 'Users') ?></h1>

<?php 
$search_action = '/user/index';
$form = $this->beginWidget('CActiveForm', array(
    'method' => 'get',         
    'action' => $this->createUrl($search_action), 
    'htmlOptions' => array(
        'class' => 'usersearchform', 
    ), 
)); 
?>
<?php echo CHtml::label(Yii::t('app', 'Search:'), FALSE) ?></b>
<?php echo CHtml::textField('searchstring', ''); ?>
<?php echo CHtml::submitButton(Yii::t('app', 'Search')); ?>
<?php $this->endWidget(); ?>   



<?php 
$urlExpression = '"/user/update/" . $data->id';
$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider, 
    'columns' => array(

        array(
            'class' => 'CLinkColumn', 
            'labelExpression' => '$data->email', 
            'urlExpression' => $urlExpression, 
            'header' => Yii::t('auth', 'Email'), 
        ), 

    ), 
));

