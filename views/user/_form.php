<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'user-form', 
    'enableAjaxValidation'=>false, 
)); ?>

<?php echo Yii::t('app', '<p class="note">Fields with <span class="required">*</span> are required.</p>'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->labelEx($model, 'email'); ?>
        <?php echo $form->textField($model, 'email', array('size'=>60, 'maxlength'=>255)); ?>
        <?php echo $form->error($model, 'email'); ?>
    </div>


    <div class="row">
        <?php echo $form->labelEx($model, 'password'); ?>
        <?php echo CHtml::passwordField('password', '', array('maxlength'=>255)); ?>
        <?php echo $form->error($model, 'password'); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label(Yii::t('auth', 'Confirm'), 'password_confirm'); ?>
        <?php echo CHtml::passwordField('password_confirm', '', array('maxlength'=>255)); ?>
    </div>

<?php
if(Yii::app()->user->checkAccess('userAssignRoles'))
{
?>
    <h2><?php echo Yii::t('auth', 'Roles'); ?> </h2>

<ul>
<?php
    $availableRoles = CHtml::listData(Yii::app()->authManager->roles, 'name', 'description');
    foreach($availableRoles as $roleName => $roleDescription)
    {
        if(strlen(trim($roleDescription)))
        {
            $availableRoles[$roleName] = $roleName . ' (' . lcfirst($roleDescription) . ')';
        }
        else
        {
            $availableRoles[$roleName] = $roleName;
        }
    }

echo $form->checkBoxList(
    $model, 
    'authItemIds', 
    $availableRoles, 
    array(
        'template'=>'<li>{input} {label}</li>', 
    )
);
?>
        <?php echo $form->error($model, 'authItemIds'); ?>
</ul>


<?php } // can this user assign roles? ?>

    <div class="row buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save')); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->
