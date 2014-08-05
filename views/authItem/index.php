<?php

$title = Yii::t('ica_auth', 'Auth Items');
$this->breadcrumbs=array(
    Yii::t('ica_auth', 'Auth Items'), 
);

if(isset($_REQUEST['type']))
{
    $name = '';
    if($_REQUEST['type'] == AuthItem::$ITEMTYPE_OPERATION)
    {
        $name = Yii::t('ica_auth', 'operation');
    }
    if($_REQUEST['type'] == AuthItem::$ITEMTYPE_TASK)
    {
        $name = Yii::t('ica_auth', 'task');
    }
    if($_REQUEST['type'] == AuthItem::$ITEMTYPE_ROLE)
    {
        $name = Yii::t('ica_auth', 'role');
    }

    if($name != '')
    {
        $title .= Yii::t('auth', ' of type {requestedTypeName}', array('{requestedTypeName}' => Yii::t('auth', $name)));
    }
    $this->breadcrumbs = array(
        Yii::t('auth', 'Auth Items') => '/authItem/index', 
        Yii::t('auth', $name), 
    );
}

if(isset($_REQUEST['searchstring']))
{ 
    $title .= Yii::t('auth', ' and search term "{search_term}"', array('{search_term}' => CHtml::encode($_REQUEST['searchstring'])));
}

?>

<h1><?php echo $title; ?></h1>


<div class="auth-item-search-form">
<?php 
$search_action = '/authItem/index';
if(isset($_REQUEST['type']) && preg_match('/^\d+$/', $_REQUEST['type']))
{ 
    $search_action .= "/type/" . $_REQUEST['type'];
} 
$form = $this->beginWidget('CActiveForm', array(
    'method' => 'get',         
    'action' => $this->createUrl($search_action), 
)); 
?>

    <b><?php Yii::t('app', 'Search:') ?></b>
<?php echo CHtml::textField('searchstring', ''); ?>
<?php echo CHtml::submitButton(Yii::t('app', 'Search')); ?>


<?php $this->endWidget(); ?>   
</div>





<?php
$urlExpression = '"/authItem/update/name/" . $data->name';
$columns = array(
    array(
        'class' => 'CLinkColumn', 
        'labelExpression' => '$data->name', 
        'urlExpression' => $urlExpression, 
        'header' => 'Name', 
    ), 
);
if(! isset($_REQUEST['type']))
{
    $columns[] = array(
        'class' => 'CLinkColumn', 
        'header' => 'Type', 
        'labelExpression' => '$data->typeName', 
        'urlExpression' => '"/authItem/index/type/" . $data->type', 
    );
};

$columns[] = array(
    'class' => 'CLinkColumn', 
    'labelExpression' => '$data->description', 
    'urlExpression' => $urlExpression, 
    'header' => 'Description', 
);

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider, 
    'columns' => $columns, 
));

