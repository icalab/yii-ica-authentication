<?php
/* @var $this PasswordResetController */
/* @var $user User */
/* @var $request Request */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'password-reset-form',
    'enableAjaxValidation'=>false,
    'action' => $this->createUrl('reset'),
)); ?>

<div class="row explanation">
<?php echo $form->hiddenField($user, 'email') ?>
<?php echo $form->hiddenField($request, 'reset_token') ?>
<?php
echo Yii::t('ica_auth', 'Reset password for account with email address "{email}".',
    array('{email}' => $user->email));
?>
</div>


    <?php echo $form->errorSummary($user);?>

    <div class="row">
        <?php echo $form->labelEx($user, 'pass'); ?>
        <?php echo $form->passwordField($user, 'pass', array('size'=>15, 'maxlength'=>255, 'value' => '')); ?>
        <?php echo $form->error($user, 'pass'); ?>
    </div>

    <div class="row">
        <?php echo CHtml::label(Yii::t('ica_auth', 'Repeat password'), 'ICAUser_pass_confirm'); ?>
        <?php echo CHtml::passwordField('ICAUser[pass_confirm]', '', array('size'=>15, 'maxlength'=>255));?>
    </div>



    <div class="row buttons">
        <?php echo CHtml::submitButton(Yii::t('ica_auth', 'Reset password')); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->

