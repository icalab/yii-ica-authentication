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
<title>Wachtwoord resetten</title>
<style type="text/css">
body {
font-family: Helvetica, Arial, sans-serif;
}
</style>
</head>
<body>

Beste gebruiker,

<br /><br />

Iemand, waarschijnlijk uzelf, heeft aangegeven een nieuw wachtwoord te willen instellen van het account dat u gebruikt voor <?php echo Yii::app()->name; ?>

<br /><br />

U kunt dit doen door te klikken op deze link:

<br /><br />

<a href="<?php echo $url; ?>">
<?php echo $url; ?>
</a>

<br /><br />

Of door de url hieronder te kopieren naar de adresbalk van uw browser:

<br /><br />

<a href="<?php echo $url; ?>">
<?php echo $url; ?>
</a>

<br /><br />

Mocht u geen nieuw wachtwoord willen instellen dan hoeft u verder niets te doen. U kunt deze mail dan als niet verzonden beschouwen.

<br /><br />

Met vriendelijke groet,

<br /><br />

Het team van <?php echo Yii::app()->name; ?>

</body>
</html>
