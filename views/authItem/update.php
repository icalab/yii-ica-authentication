<?php
$this->breadcrumbs=array(
    'Auth Items'=>array('index'), 
    $model->name, 
);

?>

<h1>Update auth item <?php echo $model->name; ?></h1>

<?php
echo $this->renderPartial('_form', array('model'=>$model)); 



