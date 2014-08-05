<?php

/**
 * This is the model class for table "AuthItem".
 *
 * The followings are the available columns in table 'AuthItem':
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $bizrule
 * @property string $data
 */
class AuthItem extends CActiveRecord
{

    public static $ITEMTYPE_OPERATION = 0;
    public static $ITEMTYPE_TASK = 1;
    public static $ITEMTYPE_ROLE = 2;


    /**
     * Returns the static model of the specified AR class.
     * @return AuthItem the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'AuthItem';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, type', 'required'), 
            array('type', 'numerical', 'integerOnly'=>true), 
            array('name', 'length', 'max'=>64), 
            array('name', 'unique'), 
            array('name', 'match', 'pattern' => '/^[a-zA-Z0-9]+$/'), 
            array('type', 'in', 'range' => range(0, 2)), 
            array('type', 'typeCannotInterfereWithChildrenOrParents'), 
            array('description, bizrule', 'safe'), 
        );
    }

    /**
     * Return a textual representation of the auth item type.
     * @return a textual representation of the aut item type
     */
    public function getTypeName() {
        if($this->type == AuthItem::$ITEMTYPE_OPERATION)
        {
            return Yii::t('ica_auth', 'operation');
        }
        if($this->type == AuthItem::$ITEMTYPE_TASK)
        {
            return Yii::t('ica_auth', 'task');
        }if($this->type == AuthItem::$ITEMTYPE_ROLE)
        {
            return Yii::t('ica_auth', 'role');
        }
        return '';
    }

    /**
     * Make sure the type is less than or equal to the type of the parent with 
     * the smallest type, greater than or equal to the child with the 
     * largest type and greater than "operation" if there are children
     */
    public function typeCannotInterfereWithChildrenOrParents($attribute, $parameters)
    {

        // For code coverage reasons (XDebug/PHPUnit can't correctly
        // determine the code coverage % for code with returns inside
        // if-blocks) we'll use a status that gets returned at the end.
        // Which is marginally more inefficient than simply returning.
        // Bwegh.
        $status = true;

        if(count($this->authItemChild) > 0)
        {
            $highestChildType = 0;
            foreach($this->authItemChild as $child)
            {
                if($child->type > $highestChildType)
                {
                    $highestChildType = $child->type;
                }
            }
            if($highestChildType > $this->type)
            {
                $this->addError($attribute, Yii::t('ica_auth', "The selected type is invalid as there are children that can not have a parent of this type."));
                $status = false;
            }
        }

        if(count($this->authItemParent) > 0)
        { 
            $lowestParentType = 2;
            foreach($this->authItemParent as $parent)
            {
                if($parent->type < $lowestParentType)
                {
                    $lowestParentType = $parent->type;
                }

            }
            if($lowestParentType < $this->type)
            {
                $this->addError($attribute, "The selected type is invalid as there are parents that cannot have a child of this type.");
                $status = false;
            }
        }

        return $status;

    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // The API does not tell us which parents a child has. So we're forced 
        // to manually dig through the database.
        return array(
            'authItemParent' => array(self::MANY_MANY, 'AuthItem', 'AuthItemChild(child, parent)', 'order' => 'type'), 
            'authItemChild' => array(self::MANY_MANY, 'AuthItem', 'AuthItemChild(parent, child)', 'order' => 'type'), 
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'name' => Yii::t('ica_auth', 'Name'), 
            'type' => Yii::t('ica_auth', 'Type'), 
            'description' => Yii::t('ica_auth', 'Description'), 
            'bizrule' => Yii::t('ica_auth', 'Bizrule'), 
            'data' => Yii::t('ica_auth', 'Data'), 
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('name', $this->name, true);
        $criteria->compare('type', $this->type);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('bizrule', $this->bizrule, true);
        $criteria->compare('data', $this->data, true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria, 
        ));
    }

    /**
     * Assign the current record to a parent.
     */
    public function attachToParent($parent)
    {
        $auth=Yii::app()->authManager;
        try
        {
            $auth->addItemChild($parent, $this->name);
        }
        catch(Exception $e)
        {
            // Don't bother testing capturing programmer errors.
            // @codeCoverageIgnoreStart
            Yii::log($e->getMessage(), 'error');
            $this->addError(null, $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

    }

    /**
     * Detach the current record from a given parent.
     */
    public function detachFromParent($parent)
    {
        $auth=Yii::app()->authManager;
        try {
            $auth->removeItemChild($parent, $this->name);
        }
        catch(Exception $e)
        {
            // Don't bother testing capturing programmer errors.
            // @codeCoverageIgnoreStart
            Yii::log($e->getMessage(), 'error');
            $this->addError(null, $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

    }

    /**
     * Assign the current record to a child.
     */
    public function attachChild($child)
    {
        $auth=Yii::app()->authManager;
        try
        {
            $auth->addItemChild($this->name, $child);
        }
        catch(Exception $e)
        {
            // Don't bother testing capturing programmer errors.
            // @codeCoverageIgnoreStart
            Yii::log($e->getMessage(), 'error');
            $this->addError(null, $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

    }

    /**
     * Detach a given child from the current record.
     */
    public function detachChild($child)
    {
        $auth=Yii::app()->authManager;
        try {
            $auth->removeItemChild($this->name, $child);
        }
        catch(Exception $e)
        {
            // Don't bother testing capturing programmer errors.
            // @codeCoverageIgnoreStart
            Yii::log($e->getMessage(), 'error');
            $this->addError(null, $e->getMessage());
            // @codeCoverageIgnoreEnd
        }

    }

}
