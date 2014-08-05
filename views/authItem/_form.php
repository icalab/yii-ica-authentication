<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'auth-item-form', 
    'enableAjaxValidation'=>false, 
)); ?>

<?php echo Yii::t('app', '<p class="note">Fields with <span class="required">*</span> are required.</p>'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model, 'name'); ?>
        <?php echo $form->textField($model, 'name', array('size'=>60, 'maxlength'=>64)); ?>
        <?php echo $form->error($model, 'name'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'type'); ?>
<?php
$options = array(NULL => '--select--');
foreach($this->itemTypes as $value => $name )
{
    $options[$value] = $name;
}

            echo $form->dropDownList($model, 'type', $options );
        ?>
        <?php echo $form->error($model, 'type'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'description'); ?>
        <?php echo $form->textArea($model, 'description', array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model, 'description'); ?>
    </div>

<?php
if(Yii::app()->user->checkAccess('authItemEditBizRule'))
{
?>
    <div class="row">
        <?php echo $form->labelEx($model, 'bizrule'); ?>
        <?php echo $form->textArea($model, 'bizrule', array('rows'=>6, 'cols'=>50)); ?>
        <?php echo $form->error($model, 'bizrule'); ?>
    </div>
<?php } // can this user edit biz rules? ?>





<?php 
if(! $model->isNewRecord)
{
?>

    <h2><?php echo Yii::t('auth', 'Parents') ?></h2>

<div class="auth_item_parents">
<?php
$urlExpression = '"/authItem/update/name/" . $data->name';
$deleteButtonCode = 'CHtml::submitButton(Yii::t("auth", "Remove"), array("submit" => "/authItem/update/name/' . $model->name . '" , "params" => array("detach_from_parent" => $data->name), "confirm" => Yii::t("auth", "Are you sure?")));';
$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider' => new CArrayDataProvider($model->authItemParent, 
        array(
            'id' => 'parents', 
            'keyField' => 'name', 
        )), 
    'columns' => array(
        array(
            'value' => '$data->typeName', 
            'header' => Yii::t('auth', 'Type'), 
        ), 
        array(
            'class' => 'CLinkColumn', 
            'labelExpression' => '$data->name', 
            'urlExpression' => $urlExpression, 
            'header' => Yii::t('auth', 'Name'), 
        ), 
        array(
            'class' => 'CLinkColumn', 
            'labelExpression' => '$data->description', 
            'urlExpression' => $urlExpression, 
            'header' => Yii::t('auth', 'Description'), 
        ), 
        array(
            'type' => 'raw', 
            'value' => $deleteButtonCode, 
        ), 

    ), 
));

/**
* Construct a list of possible parent candidates.
*/
// Operations can only belong to tasks or roles, tasks can belong to other 
// tasks or roles and roles can only belong to other roles.
$parentMinimumLevel = AuthItem::$ITEMTYPE_TASK;
if($model->type && ($model->type >= AuthItem::$ITEMTYPE_TASK))
{
    $parentMinimumLevel = $model->type;
}
    
$criteria = new CDbCriteria(array(
    'order' => 'LOWER(name)', 
));
$criteria->addCondition('type >= ' . $parentMinimumLevel);
foreach($model->authItemParent as $parent)
{
    $criteria->addCondition("name != '" .$parent->name . "'");
}

$criteria->addCondition("name != '" . $model->name . "'");
$availableParents = CHtml::listData(AuthItem::model()->findAll($criteria), 'name', 'name', 'typeName');
echo CHtml::label(Yii::t('auth', "Add parent"), 'attach_to_parent');
if(count($availableParents))
{
    echo CHtml::dropDownList('attach_to_parent', '', $availableParents, array('prompt' => Yii::t('auth', '-- select --'), ));
    echo CHtml::submitButton(Yii::t('auth', 'Add'));
}
// There are no parent candidates left to add.
else
{
    echo Yii::t('auth', '<span class="auth_item_no_parent_candidates_warning">There are no parent candidates left to add.</span>');
}

?>
</div>


<h2><?php echo Yii::t('auth', 'Children') ?></h2>

<?php
if($model->type <= AuthItem::$ITEMTYPE_OPERATION && ! ($model->authItemChild || count($model->authItemChild)))
{
?>
   <?php echo Yii::t('auth', '<span class="auth_item_children_no_children_warning">It is not possible to attach children to this item.</span>'); ?>
<?php
}
else
{
?>

<div class="auth_item_childen">
<?php
$urlExpression = '"/authItem/update/name/" . $data->name';
$deleteButtonCode = 'CHtml::submitButton(Yii::t("auth", "Remove"), array("submit" => "/authItem/update/name/' . $model->name . '" , "params" => array("detach_child" => $data->name), "confirm" => Yii::t("auth", "Are you sure?")));';
$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider' => new CArrayDataProvider($model->authItemChild, 
        array(
            'id' => 'children', 
            'keyField' => 'name', 
        )), 
    'columns' => array(
        array(
            'value' => '$data->typeName', 
            'header' => Yii::t('auth', 'Type'), 
        ), 
        array(
            'class' => 'CLinkColumn', 
            'labelExpression' => '$data->name', 
            'urlExpression' => $urlExpression, 
            'header' => Yii::t('auth', 'Name'), 
        ), 
        array(
            'class' => 'CLinkColumn', 
            'labelExpression' => '$data->description', 
            'urlExpression' => $urlExpression, 
            'header' => Yii::t('auth', 'Description'), 
        ), 
        array(
            'type' => 'raw', 
            'value' => $deleteButtonCode, 
        ), 

    ), 
));

$criteria = new CDbCriteria(array(
    'order' => 'LOWER(name)', 
));
$criteria->addCondition("type <=" . $model->type);
foreach($model->authItemChild as $child)
{
    $criteria->addCondition("name != '" .$child->name . "'");
}

$criteria->addCondition("name != '" . $model->name . "'");
$availableChildren = CHtml::listData(AuthItem::model()->findAll($criteria), 'name', 'name', 'typeName');
echo CHtml::label(Yii::t('auth', "Add child"), 'attach_child');
if(count($availableChildren))
{
    echo CHtml::dropDownList('attach_child', '', $availableChildren, array('prompt' => Yii::t('auth', '-- select --'), ));
    echo CHtml::submitButton(Yii::t('auth', 'Add'));
}
// There are no child candidates left to add.
else
{
    echo Yii::t('auth', '<span class="auth_item_no_child_candidates_warning">There are no child candidates left to add.</span>');
}

?>
</div>







<?php } // can model have children? ?>








<?php } // update or create? ?>


    <div class="row buttons">
<?php 
if(! $model->isNewRecord)
{
    echo CHtml::submitButton('Delete', array(
        'submit' => '/authItem/delete/name/' . $model->name, 
        'confirm' => Yii::t('auth', 'Are you sure?'), 
        'class' => 'auth-item-item-delete-button'
    ));
}
?>


        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'auth-item-item-save-button')); ?>
    </div>







<?php $this->endWidget(); ?>

</div><!-- form -->

