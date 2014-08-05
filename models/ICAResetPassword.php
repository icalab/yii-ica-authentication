<?php

/**
 * This is the model class for table "password_reset".
 *
 * The followings are the available columns in table 'password_reset':
 * @property integer $id
 * @property integer $userid
 * @property string $created
 * @property string $request_token
 * @property string $reset_token
 *
 * The followings are the available model relations:
 * @property User $user
 */
class ICAResetPassword extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'icaresetpassword';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('userid', 'numerical', 'integerOnly'=>true),
            array('created','default',
                'value'=>new CDbExpression('NOW()'),
                'setOnEmpty'=>false,'on'=>'insert'),
            // ORDER MATTERS! This should go after the default
            // rule for created to make sure created is
            // given a default value before it's checked
            // for not-null-ness
            array('userid, created, request_token', 'required'),
            array('request_token, reset_token', 'length', 
                'max'=>512),
                );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'ICAUser', 'userid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'userid' => 'Userid',
            'created' => 'Created',
            'request_token' => 'Request Token',
            'reset_token' => 'Reset Token',
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return PasswordReset the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Set the value of reset_token.
     */
    public function generateResetToken()
    {
        $this->reset_token = $this->generateToken();
    }

    protected function afterConstruct()
    {
        // Automatically generate a request_token.
        $this->request_token = $this->generateToken();

    }

    /**
     * Helper function for generating tokens.
     * @return
     *      a token
     */
    private function generateToken()
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $seed = '';
        for($i = 0; $i < 128; $i++)
        {
            $seed .= $chars[rand(0, strlen($chars) -1)];
        }
        return md5(uniqid($seed, true));
    }

}
