<?php

class AuthItemController extends Controller
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
            array('allow', // only users with the correct permissions can edit auth items
                'roles' => array('authItemEditor'), 
            ), 
            array('deny',  // deny all users
                'users'=>array('*'), 
            ), 
        );
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model=new AuthItem;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['AuthItem']))
        {
            $model->attributes=$_POST['AuthItem'];
            if($model->save())
            {
                $this->redirect(array('update', 'name'=>$model->name));
            }
        }

        $this->render('create', array(
            'model'=>$model, 
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($name)
    {

        $model=$this->loadModel($name);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        $isUpdated = FALSE;

        if(isset($_POST['AuthItem']))
        {
            $model->attributes=$_POST['AuthItem'];
            if($model->save())
            {
                $isUpdated = TRUE;
            }
        }

        // Attach and detach children and parents
        foreach(array(
            'detach_from_parent' => 'detachFromParent', 
            'attach_to_parent' => 'attachToParent', 
            'detach_child' => 'detachChild', 
            'attach_child' => 'attachChild', 
            ) as $inputName => $methodName){
            if(isset($_POST[$inputName]) && strlen($_POST[$inputName]))
            {
                $model->$methodName($_POST[$inputName]);
                $isUpdated = TRUE;
            }
        }

        if($isUpdated && ! $model->hasErrors())
        {
            $this->redirect(array('update', 'name'=>$model->name));
        }


        $this->render('update', array(
            'model'=>$model, 
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($name)
    {
        if(Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $this->loadModel($name)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
            {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
            }
        }
        else
        {
            throw new CHttpException(400, 
                'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex($type = NULL)
    {
        $criteria = new CDbCriteria(array(
            'order' => 'LOWER(name) DESC', 
            //'order' => 'type, LOWER(name) DESC', 
        ));


        if(isset($_REQUEST['searchstring']))
        {
            $criteria->compare('name', $_REQUEST['searchstring'], true, 'OR');
            $criteria->compare('description', $_REQUEST['searchstring'], true, 'OR');
        }

        if($type !== null
            && $type != AuthItem::$ITEMTYPE_OPERATION
            && $type != AuthItem::$ITEMTYPE_TASK
        && $type != AuthItem::$ITEMTYPE_ROLE)
        {
            throw new CHttpException(404, 'Unknown type.');
        }
        if($type !== null)
        {
            $criteria->addCondition('type=' . $type);
        }
        $dataProvider=new CActiveDataProvider('AuthItem', array('criteria' => $criteria));
        $this->render('index', array(
            'dataProvider'=>$dataProvider, 
        ));
    }

    /**
     * Provide PHP code for generating auth data
     */
    public function actionDownload()
    {

        $data = array();
        foreach($this->itemTypes as $typeId => $typeName)
        {
            $data[$typeName] = AuthItem::model()->findAll('type=' . $typeId);
        }
        $this->renderPartial('download', $data);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($name)
    {
        $model=AuthItem::model()->findByPk($name);
        if($model===null)
        {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='auth-item-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    private $_itemTypes = null;

    public function getItemTypes()
    {
        if($this->_itemTypes === null)
        {
            $this->_itemTypes = array(
                AuthItem::$ITEMTYPE_OPERATION => Yii::t('ica_auth', 'operation'),
                AuthItem::$ITEMTYPE_TASK => Yii::t('ica_auth', 'task'),
                AuthItem::$ITEMTYPE_ROLE => Yii::t('ica_auth', 'role'),

            );
        }
        return $this->_itemTypes;
    }

    protected function beforeRender($view)
    {
        foreach($this->itemTypes as $id => $name)
        {
            $this->menu[] = array(
                'label' => Yii::t('auth', 'List items of type "' . $name  . '"'),
                'url' => '/authItem/index/type/' . $id,
            );
        };
        $this->menu[] = array(
            'label' => Yii::t('ica_auth', 'List all items'),
            'url' => '/authItem/index',
        );

        $this->menu[] = array(
            'label' => Yii::t('ica_auth', 'Export items'),
            'url' => '/authItem/download',
        );
        if($view != 'create')
        {
            $this->menu[] = array(
                'label' => Yii::t('ica_auth', 'Create new item'),
                'url' => '/authItem/create',
            );
        }
        return parent::beforeRender($view);
    }



}
