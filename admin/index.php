<?
/**
 * This program helps manage person's user profile using Semantic Web standards such as FOAF
 */

$SPROOT = '../';

/**
 * Configuration parameters
 */
include_once('../config.inc.php');

/**
 * Uncomment to enable debugging functionality
 */
#include_once('debug.php');

include_once('../global_functions.inc.php');

$model = getModel();
$personURI = getPrimaryPerson($model);

include_once('modules/base.inc.php');

$module = $modules[0];

foreach ($modules as $mod)
{
	if ($mod->getSlug() == $_REQUEST['module'])
	{
		$module = $mod;
	}
}

$languages = array(
	array('code' => 'en', 'label' => 'English'),
	array('code' => 'ru', 'label' => 'Russian'),
	array('code' => 'fr', 'label' => 'French')
);

$lang = $languages[0]['code'];

if (array_key_exists('lang', $_REQUEST))
{
	$lang = $_REQUEST['lang'];
}

/**
 * Displaying tabs with module names linking to modules
 */
?><div id="navigation">
<form action="./" method="POST">
<input name="module" type="hidden" value="<?=urlencode($module->getSlug())?>">
<?
foreach ($modules as $mod)
{
	if ($mod == $module)
	{
		?><span class="active tab" id="nav_<?=$mod->getSlug()?>"><?=$mod->getName()?></span>
<?
	}
	else
	{
		?><a class="tab" id="nav_<?=$mod->getSlug()?>" href="./?<? if ($mod != $modules[0]) { ?>module=<?=urlencode($mod->getSlug())?><? } ?>&lang=<?=$lang?>"><?=$mod->getName()?></a>
<?
	}
}
?><select name="lang" onchange="location = './?<? if ($module != $modules[0]) { ?>module=<?=urlencode($module->getSlug())?>&<? } ?>lang='+this.options[this.selectedIndex].value;"><?
foreach ($languages as $language)
{
	?><option value="<?=$language['code'] ?>"<? if ($language['code'] == $lang) {?> selected<?}?>><?=$language['label'] ?></option>
<?
}
?>
</select>
</div>

<?
if (array_key_exists('save', $_POST))
{
	if ($module->saveChanges($model, $personURI))
	{
		header( 'Location: ./?saved=success&lang='.$lang.($module != $modules[0] ? '&module='.urlencode($module->getSlug()) : '' )) ;
		exit;
	}
}

$module->displayForm($model, $personURI, $lang);
?>
<input type="submit" name="save" value="Save changes">
</form>
<?
