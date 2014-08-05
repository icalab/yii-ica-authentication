<?php

class UserController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', 
                'actions'=>array('index', 'create', 'update'), 
                'roles'=>array('userManager'), 
            ), 
            array('deny',  // deny all users
                'users'=>array('*'), 
            ), 
        );
    }

    /**
     * Create a new user.
     * If creation is successful, the browser will be redirected to the 'update' page.
     */
    public function actionCreate()
    {
        $model = new ICAUser;
        $model->password = uniqid('empty', true); // password is a required field. make sure to set it.

        if(isset($_POST['ICAUser']))
        {
            $password = NULL;
            if(isset($_POST['password']))
            {
                if(isset($_POST['password_confirm']) && ($_POST['password_confirm'] == $_POST['password']))
                {
                    $password = $_POST['password'];
                }
                else
                {
                    $model->attributes=$_POST['ICAUser'];
                    $model->validate();
                    $model->addError('password', Yii::t('ica_auth', 'Passwords do not match'));
                    $this->render('create', array('model' => $model));
                    return;

                }
            }

            $model->attributes=$_POST['ICAUser'];
            if($model->save())
            {
                if($password)
                {
                    $model->setPassword($password);
                }
                $this->redirect(array('update', 'id'=>$model->id));
            }
        }

        $this->render('create', array(
            'model'=>$model, 
        ));
    }

    /**
     * Updates a user.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model=$this->loadModel($id);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['ICAUser']))
        {
            $model->attributes=$_POST['ICAUser'];
            $password = NULL;
            $canSave = $model->validate();
            if(isset($_POST['password']))
            {
                if(isset($_POST['password_confirm']) && ($_POST['password_confirm'] == $_POST['password']))
                {
                    $password = $_POST['password'];
                }
                else
                {
                    $model->addError('password', Yii::t('ica_auth', 'Passwords do not match'));
                    $canSave = FALSE;
                }
            }
            if($canSave)
            {
                $model->save();
                if($password)
                {
                    $model->setPassword($password);
                }
                $this->redirect(array('update', 'id'=>$model->id));
            }
        }

        $this->render('update', array(
            'model'=>$model, 
        ));
    }

   /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $criteria = new CDbCriteria(array(
            'order' => 'LOWER(email) DESC', 
        ));


        if(isset($_REQUEST['searchstring']))
        {
            $criteria->compare('email', $_REQUEST['searchstring'], true, 'OR');
        }


        $dataProvider=new CActiveDataProvider('ICAUser', array('criteria' => $criteria));
        $this->render('index', array(
            'dataProvider'=>$dataProvider, 
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model=ICAUser::model()->findByPk($id);
        if($model===null)
        {
            throw new CHttpException(404, Yii::t('The requested page does not exist.'));
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
