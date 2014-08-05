<?php
header('Content-type: text/plain');
?>
/**
 *
 * Authentication item export created on <?php echo date(DATE_RFC822); ?> 
 *
 * Copy and paste this into a script that runs in a Yii context.
 *
 * Note this script does NOT clear any previously created authentication data.
 *
 */

$auth=Yii::app()->authManager;

// First create all AuthItems
<?php
foreach($this->itemTypes as $typeId => $typeName)
{
    $functionName = 'create' . ucfirst($typeName);
    foreach($$typeName as $item)
    {
        echo "\$auth->$functionName('".$item->name."');\n";
    }
}
?>

// Now set all relations

<?php
foreach($this->itemTypes as $typeId => $typeName)
{
    foreach($$typeName as $item)
    {
        foreach($item->authItemChild as $child)
        {
            echo "\$auth->addItemChild('".$item->name."', '".$child->name."');\n";
        }
    }
}

