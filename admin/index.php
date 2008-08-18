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

if ($personURI == null)
{
	header( 'Location: ./init.php');
	exit;
}

include_once('modules/base.inc.php');

$module = $modules[0];

foreach ($modules as $mod)
{
	if (array_key_exists('module', $_REQUEST) && $mod->getSlug() == $_REQUEST['module'])
	{
		$module = $mod;
	}
}

$lang = $defaultlang;

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
?><select name="lang" onchange="if (this.options[this.selectedIndex].value.indexOf('-') != 0) {location = './?<? if ($module != $modules[0]) { ?>module=<?=urlencode($module->getSlug())?>&<? } ?>lang='+this.options[this.selectedIndex].value; }"><?

?><option value="<?=$defaultlang ?>"<? if ($lang == $defaultlang) { ?> selected<?}?>><?=(array_key_exists($defaultlang, $languageParams) ? $languageParams[$defaultlang]['label'] : $defaultlang) ?> (default)</option><?

$model_languages = getModelLanguages($model);

unset($model_languages[$defaultlang]); // we'll show default language first
unset($model_languages['']); // no need to show non-defined language (we assume it's the same as default)

/*
 * If there is more then one language present
 */
if (count($model_languages))
{
	foreach (array_keys($model_languages) as $l)
	{
		?><option value="<?=$l ?>"<? if ($lang == $l) {?> selected<?}?>><?=(array_key_exists($l, $languageParams) ? $languageParams[$l]['label'] : $l) ?></option><?
	}
}

/*
 * Now, if selected language is not default and not already in the model, then just show it
 */
if(!array_key_exists($lang, $model_languages) && $lang != $defaultlang)
{
	?><option value="<?=$language ?>" selected><?=(array_key_exists($lang, $languageParams) ? $languageParams[$lang]['label'] : $lang) ?></option><?
}

/*
 * Show more languages we know about in the dropdown below
 */
?><option>-- add new --</option>
<?
foreach ($languageSequence as $language)
{
	if ($language != $defaultlang && !array_key_exists($language, $model_languages) && $language != $lang)
	{
		?><option value="<?=$language ?>"><?=$languageParams[$language]['label'] ?></option><?
	}
}
?>
</select>

<span id="viewnav">View: <a href="../">HTML page</a> |  <a href="<?=$profileDocumentURI?>">RDF</a> | <a href="<?=$personURI->getURI()?>">Main URI</a></span>
</div>

<?
if (array_key_exists('save', $_REQUEST))
{
	$success = ($module->saveChanges($model, $personURI, $lang) && saveModel()) ? 'success' : 'failure';

	header( 'Location: ./?saved='.$success.'&lang='.$lang.($module != $modules[0] ? '&module='.urlencode($module->getSlug()) : '' )) ;
	exit;
}

$module->displayForm($model, $personURI, $lang);
?>
<input type="submit" name="save" value="Save changes">
</form>
