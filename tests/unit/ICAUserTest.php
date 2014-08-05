<?php
/**
 * Unit tests for the ICAUser models (ICAUser, AuthItem, ICAResetPassword).
 */
class ICAUserTest extends CDbTestCase
{
    private static $refCounter = 0;
    /**
    * Setup the database.
    * @large
    */
    public static function setUpBeforeClass()
    {
        self::$refCounter++;
        if(self::$refCounter > 1)
        {
            return;
        }

        $connection=Yii::app()->db;

        $command = $connection->createCommand();
        $command->createTable('icauser', array(
            'id' => 'pk',
            'email' => 'VARCHAR(512) NOT NULL',
            'salt' => 'VARCHAR(255) NOT NULL',
            'password' => 'VARCHAR(255) NOT NULL',), null);

        $command->createTable('icaresetpassword', array(
            'id' => 'pk',
            'created' => 'TIMESTAMP',
            'userid' => 'INT NOT NULL',
            'request_token' => 'VARCHAR(512) NOT NULL',
            'reset_token' => 'VARCHAR(512) NULL',
            ), null);
        $command->addForeignKey('fk_icaresetpassword_userid',
            'icaresetpassword',
            'userid',
            'icauser',
            'id', 'CASCADE', 'CASCADE');

        $command->createTable('AuthItem', array(
            'name' => 'VARCHAR(64) NOT NULL PRIMARY KEY',
            'type' => 'INTEGER NOT NULL',
            'description' => 'TEXT',
            'bizrule' => 'TEXT',
            'data' => 'TEXT',), null);

        $command->createTable('AuthItemChild', array(
            'parent' => 'VARCHAR(64) NOT NULL',
            'child' => 'VARCHAR(64) NOT NULL',
            'PRIMARY KEY (parent, child)',
        ), null);
        $command->addForeignKey('fk_AuthItemChild_parent',
            'AuthItemChild',
            'parent',
            'AuthItem',
            'name', 'CASCADE', 'CASCADE');
        $command->addForeignKey('fk_AuthItemChild_child',
            'AuthItemChild',
            'child',
            'AuthItem',
            'name', 'CASCADE', 'CASCADE');

        $command->createTable('AuthAssignment', array(
            'itemname' => 'VARCHAR(64) NOT NULL',
            'userid' => 'VARCHAR(64) NOT NULL',
            'bizrule' => 'TEXT',
            'data' => 'TEXT',
            'PRIMARY KEY (itemname, userid)'), null);
        $command->addForeignKey('fk_AuthAssignment_itemname',
            'AuthAssignment',
            'itemname',
            'AuthItem',
            'name', 'CASCADE', 'CASCADE');
    }

    /**
     * Test the generic housekeeping methods that are always present
     * in a model.
     * @medium
     */
    public function testDefaultMethods()
    {
        foreach(array(new ICAUser(), new ICAResetPassword())
            as $model)
        {
            $this->assertNotNull($model->model());
            $this->assertNotNull($model->tableName());
            $this->assertTrue(is_array($model->rules()));
            $this->assertTrue(is_array($model->relations()));
            $this->assertTrue(is_array($model->attributeLabels()));
        }
    }

    public function testCRUD ()
    {
        $user = new ICAUser();

        $password = 'password';

        $user->setAttributes(array(
            'id' => 1,
            'email' => 'johnsmith@hotmail.com',
            'password'=> $password,
        ), false);

        // Make sure all required fields are required.
        $user->password = '';
        $this->assertFalse($user->validate(), 'The password field is required.');
        $user->password = $password;
        $user->email = '';
        $this->assertFalse($user->validate(), 'The email field is required.');
        $user->email = 'nonsense';
        $this->assertFalse($user->validate(), 'The email address must be valid.');
        $user->email = 'johnsmith@hotmail.com';
        $this->assertTrue($user->validate(), 'A correct user is validated.');

        // Test password encryption and validation.
        $user->save();
        $savedUser = ICAUser::model()->findByPk($user->primaryKey);
        $this->assertNotEquals($savedUser->password, $password,
            'Passwords are encrypted.');
        $this->assertTrue($savedUser->validatePassword($password),
            'Correct passwords are validated.');
        $this->assertFalse($savedUser->validatePassword($password . $password),
            'Incorrect passwords are not validated.');


        // Test updating passwords.
        $newPassword = 'nieuwWachtwoord';
        $user->password = $newPassword;
        if(! $user->save())
        {
            $this->fail("Unable to save user: " . print_r($user->getErrors(), true));
        }
        $savedUser = ICAUser::model()->findByPk($user->primaryKey);
        $this->assertTrue($savedUser->validatePassword($newPassword),
            'Updating passwords works.');

        // Test the password confirm mechanism.
        $savedUser->passwordConfirm = 'abcde';
        $this->assertTrue($savedUser->save(),
            'Saving with password confirm set but no update to the password works.');
        $savedUser->password = 'nietNieuwWachtwoord';
        $savedUser->passwordConfirm = 'foutNieuwWachtwoord';
        $this->assertFalse($savedUser->save(),
            'Refuse to save a user with invalid password confirmation.');
        $savedUser->password = 'abcd1234';
        $savedUser->passwordConfirm = 'abcd1234';
        $this->assertTrue($savedUser->save(),
            'Save a user with valid password confirmation.');

        // Test if email addresses must be unique.
        $newUser = new ICAUser();
        $newUser->email = $savedUser->email;
        $newUser->password = 'abcdefgh';
        $this->assertFalse($newUser->save(), 'Email address must be unique.');

        $user->delete();
    }

    public function testRoleAssignment()
    {
        $user = new ICAUser();
        $user->email = 'testroles@unittest.com';
        $user->password = 'password';
        if(! $user->save())
        {
            $this->fail('Unable to create user to test roles with: '
                . print_r($user->getErrors(), true));
        }
        $user->refresh();

        $roleName = uniqid('', true);
        Yii::app()->db->createCommand()->insert('AuthItem', array (
            'name' => $roleName,
            'type' => 2,
            'description' => 'unit test role',
            'bizrule' => null,
            'data' => 'N;'));
        $user->assignRole($roleName);

        $this->assertTrue(Yii::app()->db->createCommand()
            ->select('*')->from('AuthAssignment')
            ->where('userid=:id', array(':id' => $user->primaryKey))
            ->query()->rowCount == 1, 'Assigning roles works.');

        $exceptionIsThrown = false;
        try
        {
            $user->assignRole($roleName . '.nonsense');
        }
        catch(Exception $e)
        {
            $exceptionIsThrown = true;
        }
        $this->assertTrue($exceptionIsThrown,
            'Assigning a nonexistent role throws an error.');


        $exceptionIsThrown = false;
        try
        {
            $user->assignRole($roleName);
        }
        catch(Exception $e)
        {
            $exceptionIsThrown = true;
        }
        $this->assertFalse($exceptionIsThrown,
            'Assigning the same role twice is allowed.');


        $numAssigned = Yii::app()->db->createCommand()
            ->select('*')
            ->from('AuthAssignment')
            ->where('itemname=:rolename AND userid=:id', array(
                ':rolename' => $roleName,
                ':id' => $user->primaryKey))
            ->query()->rowCount;
        $this->assertEquals(1, $numAssigned,
            'Assigning the same role twice has no effect.');

        $exceptionIsThrown = false;
        try
        {
            $user->unassignRole($roleName . '.nonsense');
        }
        catch(Exception $e)
        {
            $exceptionIsThrown = true;
        }
        $this->assertTrue($exceptionIsThrown,
            'Unassigning a nonexistent role fails.');


        $user->unassignRole($roleName);
        $numAssigned = Yii::app()->db->createCommand()
            ->select('*')
            ->from('AuthAssignment')
            ->where('itemname=:rolename AND userid=:id', array(
                ':rolename' => $roleName,
                ':id' => $user->primaryKey))
            ->query()->rowCount;
        $this->assertEquals(0, $numAssigned, 'Unassigning roles works.');


        $user->refresh();
    }

    public function testSetupUser()
    {
        Yii::app()->db->createCommand()->delete('AuthAssignment');
        Yii::app()->db->createCommand()->delete('AuthItemChild');
        Yii::app()->db->createCommand()->delete('AuthItem');
        Yii::app()->db->createCommand()->delete('icaresetpassword');
        Yii::app()->db->createCommand()->delete('icauser');

        ICAUser::setup();

        $user = ICAUser::model()->find("email='admin@admin.com'");

        $this->assertNotNull($user, 'A default admin user is created in setup.');

        foreach(array(
            'userManager',
            'authItemEditor',
            'userAssignRoles',
            'authItemEditBizRule',
        ) as $role)
        { 
            $this->assertNotNull(AuthItem::model()->find("name='$role'"),
                "The $role role is created during setup.");

            $this->assertTrue(Yii::app()->db->createCommand()->select('*')
                ->from('AuthAssignment')
                ->where("itemname='$role' AND userid=:id", 
                array(':id' => $user->primaryKey))
                ->query()->rowCount == 1,
                    "Setup assigns the default admin the $role role.");
        }

    }

    public function testResetPassword()
    {
        $user = new ICAUser();
        $user->email = uniqid('test', true) . '@server.com';
        $user->password = 'password';
        $user->save();
        $user->refresh();

        $request = new ICAResetPassword();
        $this->assertNotNull($request->request_token,
            'Request tokens are generated correctly.');
        $this->assertFalse($request->validate(),
            'Refuse to save a request without a user.');
        $request->userid = $user->primaryKey();
        $request->generateResetToken();
        $this->assertNotNull($request->reset_token,
            'Reset tokens are generated correctly.');

        // Need this test to make sure the order
        // of the rules is correct. If they arent',
        // this test fails.
        $request = new ICAResetPassword();
        $request->userid = $user->primaryKey;
        $this->assertTrue($request->validate(),
            'A valid request is validated immediately.');
   


    }



    /**
    * @large
    */
    public static function tearDownAfterClass()
    {
        self::$refCounter--;
        if(self::$refCounter > 0)
        {
            return;
        }
        Yii::app()->db->createCommand()->dropTable('AuthAssignment');
        Yii::app()->db->createCommand()->dropTable('AuthItemChild');
        Yii::app()->db->createCommand()->dropTable('AuthItem');
        Yii::app()->db->createCommand()->dropTable('icaresetpassword');
        Yii::app()->db->createCommand()->dropTable('icauser');
    }
}
