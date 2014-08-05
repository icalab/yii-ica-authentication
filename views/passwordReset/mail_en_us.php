<?php
/* @var $request Request */
?>
<?php
$url = 'http://' . Yii::app()->params['passwordResetHost']
    . '/passwordReset/request'
    . '/token/' . $request->request_token
    . '/email/' . $request->user->email;
?>
<html>
<head>
<title>Reset password</title>
<style type="text/css">
body {
font-family: Helvetica, Arial, sans-serif;
}
</style>
</head>
<body>

Dear user,

<br /><br />

Someone, presumably you, has requested a new password for the account you use for <?php echo Yii::app()->name; ?>.

<br /><br />

If you want to do this, you can click the link below:

<br /><br />

<a href="<?php echo $url; ?>">
<?php echo $url; ?>
</a>

<br /><br />

Alternatively you can copy the url below to the address bar of your browser:

<br /><br />

<a href="<?php echo $url; ?>">
<?php echo $url; ?>
</a>

<br /><br />

If you do not want to set a new password, no further action is required on your part. You can safely ignore this email message.

<br /><br />

Kind regards,

<br /><br />

The <?php echo Yii::app()->name; ?> team

</body>
</html>
