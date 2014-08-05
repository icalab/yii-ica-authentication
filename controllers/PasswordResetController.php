<?php
/**
 * Password reset controller. How it works:
 * Somewhere (in a mobile app, say) a process
 * is triggered that generates a new password reset request.
 * The resulting token is then passed along with the user's
 * email address to the "request" method. Here the
 * user can enter a new password.
 * The "reset" method makes sure the reset request is valid
 * and updates the password.
 *
 * IMPORTANT: This means you need to provide your OWN
 * method for generating reset tokens!
 */
class PasswordResetController extends Controller
{

   /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + reset',
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
                'actions'=>array('request', 'reset'),
                'users' => array('*'),
            ),
            /*
            array('deny',
                'users'=>array('*'),
            ),
             */
        );
    }

    /**
     * Display the password reset form, if the token is ok.
     * @param token
     *      the token
     * @param email
     *      the email address
     */
    public function actionRequest($token, $email)
    {
        // It is unfortunately impossible to perform code coverage
        // checking for this sanity testing mechanism.
        // It *is* tested, however.
        // @codeCoverageIgnoreStart
        $user = ICAUser::model()->find('email=:email',
            array(':email' => $email));
        if(! $user )
        {
            throw new CHttpException(404,
                Yii::t('app', 'The requested page does not exist.'));
        }

        $request = PasswordReset::model()->find(
            'request_token=:token AND userid=:id',
            array(':token' => $token, ':id' => $user->primaryKey));
        if(! $request)
        {
            throw new CHttpException(404,
                Yii::t('app', 'The requested page does not exist.'));
        }
        // @codeCoverageIgnoreEnd

        $request->generateResetToken();
        $request->save();

        $this->render('request', array(
            'user' => $user,
            'request' => $request,
        ));
    }

    public function actionReset()
    {
        // It is unfortunately impossible to perform code coverage
        // checking for this sanity testing mechanism.
        // It *is* tested, however.
        // @codeCoverageIgnoreStart
        if((! isset($_POST['ICAUser']))
            || (! isset($_POST['PasswordReset']))
            || (! isset($_POST['ICAUser']['email']))
            || (! isset($_POST['ICAUser']['pass']))
            || (! isset($_POST['ICAUser']['pass_confirm']))
            || (! isset($_POST['PasswordReset']['reset_token'])))
        {
            throw new CHttpException(400, 'Bad request');
        }

        $user = User::model()->find('email=:email',
            array(':email' => $_POST['ICAUser']['email']));
        if(! $user )
        {
            throw new CHttpException(404,
                Yii::t('app', 'The requested page does not exist.'));
        }

        $request = PasswordReset::model()->find(
            'reset_token=:token AND userid=:id',
            array(':token' => $_POST['PasswordReset']['reset_token'],
            ':id' => $user->primaryKey));
        if(! $request )
        {
            throw new CHttpException(404,
                Yii::t('app', 'The requested page does not exist.'));
        }
        // @codeCoverageIgnoreEnd

        $user->pass = $_POST['ICAUser']['pass'];
        $user->pass_confirm = $_POST['ICAUser']['pass_confirm'];
        if($user->save())
        {
            $request->delete();
            $this->render('success', array(
                'user' => $user,
            ));
        }
        else
        {
            $this->render('request', array(
                'user' => $user,
                'request' => $request,
            ));
            return;

        }

    }

}


