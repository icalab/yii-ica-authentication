<?php

/**
 * This is the model class for table "{{icauser}}".
 *
 * The followings are the available columns in table '{{user}}':
 * @property integer $id
 * @property string $email
 * @property string $salt
 * @property string $password
 * @property string $profile
 */
class ICAUser extends CActiveRecord
{

    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
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
        return 'icauser';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('password, email', 'required'), 
            array('email', 'unique', 'message' => Yii::t('ica_auth',
                'This email address is already in use.')), 
            array('email', 'email'), 
            array('password, email', 'length', 'max'=>255), 
            array('password', 'validateConfirmIfNecessary'),
            array('authItemIds, profile', 'safe'), 
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'AuthItem' => array(self::MANY_MANY, 'AuthItem', 'AuthAssignment(userid, itemname)'), 
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID', 
            'password' => Yii::t('ica_auth', 'Password'), 
            'email' => Yii::t('ica_auth', 'Email'), 
        );
    }

    protected function beforeSave() 
    {
        if($this->password != $this->previouslySavedPassword)
        {
            $this->setPassword($this->password);
        }
        return parent::beforeSave();
    }
    
    /**
     * Encrypt the supplied password.
     * @param $password the password to set.
     * @return nothing, the salt and the password are set for the current 
     * user.
     */
    public function setPassword($password)
    {
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        shuffle($chars);
        $salt = substr(implode($chars), 0, 4);

        $this->salt = $salt;
        $this->password = md5($this->salt . $password);
    }

    /**
     * Helper function for validating a password. It's
     * public so it can be used in unit tests and in 
     * controllers.
     * @param $password the password
     * @return true or false
     */
    public function validatePassword($password)
    {
        if(md5($this->salt . $password) == $this->password)
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Assign a role to the user. Useful for assigning roles
     * in a non CWebApplication-context
     * @param $roleName
     *      the name of the role to assign
     * @return nothing
     */
    public function assignRole($roleName)
    {
        if(Yii::app()->db->createCommand()
            ->select('*')
            ->from('AuthItem')
            ->where('name=:name', array(':name' => $roleName))
            ->query()->rowCount == 0)
        {
            throw new CException('Attempt to assign non-existent role.');
        }
        
        if(Yii::app()->db->createCommand()
            ->select('*')
            ->from('AuthAssignment')
            ->where('itemname=:name AND userid=:id', array(
                ':name' => $roleName,
                ':id' => $this->primaryKey))->query()->rowCount > 0)
        {
            // Don't care. The result is the same.
            return;
        }

        Yii::app()->db->createCommand()->insert('AuthAssignment', array(
            'itemname' => $roleName,
            'userid' => $this->primaryKey));
        
    }

    /**
     * Unassign a role from a user. Useful for unassigning roles in a non 
     * CWebapplication-context
     * @param $roleName
     *      the name of the role to unassign.
     */
    public function unassignRole($roleName)
    {
        if(Yii::app()->db->createCommand()
            ->select('*')
            ->from('AuthItem')
            ->where('name=:name', array(':name' => $roleName))
            ->query()->rowCount == 0)
        {
            throw new CException('Attempt to unassign from non-existent role.');
        }

        Yii::app()->db->createCommand()->delete('AuthAssignment',
            'itemname=:name AND userid=:id', 
            array(
                ':name' => $roleName,
                ':id' => $this->primaryKey));


    }


    protected function beforeDelete()
    {
        // Not our own code, strictly speaking.
        // @codeCoverageIgnoreStart
        $result = parent::beforeDelete();
        if(! $result )
        {
            return $result;
        }
        // @codeCoverageIgnoreEnd
        
        // Unassign this user from any roles he may have.
        Yii::app()->db->createCommand()->delete('AuthAssignment',
            'userid=:id', array(':id' => $this->primaryKey));
           
        return $result;
    }

 


    // Create an attribute authItemIds that contains an array of auth item 
    // names the current user is assigned to
    public $authItemIds = array();
    // Store the previously saved password so we can detect if it's changed.
    private $previouslySavedPassword = '';
    public function afterFind()
    {
        $auth=Yii::app()->authManager;
        $assignments = $auth->getAuthAssignments($this->id);
        foreach($assignments as $role => $authItem)
        {
            $this->authItemIds[] = $role;
        }

        $this->previouslySavedPassword = $this->password;

        return parent::afterFind();
    }

    /**
     * Save role assignments
     */
    public function afterSave()
    {
        $auth=Yii::app()->authManager;
        $assignments = $auth->getAuthAssignments($this->id);
        foreach($assignments as $role => $authItem)
        {
            $auth->revoke($role, $this->id);
        }
        if(empty($this->authItemIds))
        {
            return parent::afterSave();
        }

        foreach($this->authItemIds as $name)
        {
            // No biz rules for now
            $auth->assign($name, $this->id);
        }

        return parent::afterSave();
    }

    /**
     * Return users by role, task or operation
     */
    public function findAllByAuthItem()
    {
        $criteria = new CDbCriteria(array(
            'with' => 'AuthItem', 
        ));
        foreach(func_get_args() as $arg)
        {
            $criteria->addCondition("AuthItem.name='" . preg_replace('/[^a-zA-Z0-9_-]/', '', $arg) . "'", 'OR');
        };
        return self::findAll($criteria);
    }

    /**
     * Validation function for the password confirm field. Only
     * runs if an attribute password_confirm is set.
     */
    public $passwordConfirm;
    public function validateConfirmIfNecessary($attribute, $params)
    {
        if(! isset($this->passwordConfirm) )
        {
            return;
        }
        if($this->password == $this->previouslySavedPassword)
        {
            return;
        }

        if($this->$attribute != $this->passwordConfirm)
        {
            $this->addError('passwordConfirm', Yii::t('app', 'Passwords do not match.'));
        }
    }

    /**
     * Run this method once after installation to create
     * a userManager role that can be used to add further
     * users and roles and an admin account with the
     * password 'admin' and the email address 'admin@admin.com'.
     */
    public static function setup()
    {
        
        if(Yii::app()->db->createCommand()
            ->select('*')
            ->from('AuthItem')
            ->where('name=:name', array(':name' => 'userManager'))
            ->query()->rowCount != 0
            ||
            Yii::app()->db->createCommand()
            ->select('*')
            ->from('icauser')->query()->rowCount != 0)
        {
            throw new CException('Illegal attempt to run setup with data in AuthItem and icauser.');
        }

        $admin = new ICAUser();
        $admin->password = 'admin';
        $admin->email = 'admin@admin.com';
        $admin->save();

        foreach(array(
            'userManager', 
            'authItemEditor', 
            'userAssignRoles',
            'authItemEditBizRule',
        ) as $roleName)
        {
            $role = new AuthItem();
            $role->name = $roleName;
            $role->type = AuthItem::$ITEMTYPE_ROLE;
            $role->save();
            $admin->assignRole($roleName);
        }

    }

 
}
