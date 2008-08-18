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

/*
 * There is no need to init if we can already fetch person's URI
 */
if ($personURI != null)
{
	header( 'Location: ./');
	exit;
}

$lang = $defaultlang;

if (array_key_exists('save', $_REQUEST))
{
	if ($_REQUEST['init_person_uri'])
	{
		$personURI = new Resource($_REQUEST['init_person_uri']);
	}
	else
	{
		$personURI = new Resource($baseURL.'i');
	}

	if ($_REQUEST['init_person'] == '*new-user*')
	{
		$model->add(new Statement($personURI, new Resource($rdf.'type'), new Resource($foaf.'Person')));
	}
	else
	{
		$existing_uri = $_REQUEST['init_person'];
		if(strpos($existing_uri, '*blank*') === 0)
		{
			$existing_node = new BlankNode(substr($existing_uri, strlen('*blank*')));
		}
		else
		{
			$existing_node = new Resource($existing_uri);
		}

		$model->replace($existing_node, null, $existing_node, $personURI);
	}

	$docURI = new Resource("");
	$model->add(new Statement($docURI, new Resource($rdf.'type'), new Resource($foaf.'PersonalProfileDocument')));
	$model->add(new Statement($docURI, new Resource($foaf.'maker'), $personURI));
	$model->add(new Statement($docURI, new Resource($foaf.'primaryTopic'), $personURI));

	$success = saveModel() ? 'success' : 'failure';
	header( 'Location: ./init.php?saved='.$success );
	exit;
}
?>
<h1>Initialize profile</h1>
Profile has no person associated with it.

<form action="" method="POST">
<?
/*
 * No, let's check if there are any people in the model so we can offer user to pick one 
 */
$query = 'PREFIX foaf: <'.$foaf.'>
select ?uri, ?name
where {
?uri rdf:type foaf:Person .
?uri foaf:name ?name
}';
#echo "$query\n";
$people = $model->sparqlQuery($query);

if (count($people))
{
	?>
<p>Please, pick one of the people already in profile document:</p>
<select name="init_person" size="<?=(count($people) < 10 ? count($people)+1 : 11)?>" onchange="if (this.options[this.selectedIndex].value.indexOf('*blank*') != 0 && this.options[this.selectedIndex].value.indexOf('*new-user*') != 0) {form.init_person_uri.value=this.options[this.selectedIndex].value} else {form.init_person_uri.value=form.init_default_person_uri.value}">
<option value="*new-user*">-- create new person --</option>
<?
	foreach ($people as $person)
	{
		?><option value="<?=(is_a($person['?uri'], 'BlankNode') ? '*blank*' : '')?><?=$person['?uri']->getURI()?>"><?=($person['?name'] ? $person['?name']->getLabel() : $person['?uri']->getURI())?></option>
<?
	}
?></select>
<?
}
?>
<h2>Pick URI</h2>
<p>Enter URI for new user to be created<br/>
(default is <b><?=htmlspecialchars($baseURL.'i')?></b>)</p>
<input type="text" size="80" name="init_person_uri" value="<?=htmlspecialchars($baseURL.'i')?>">
<input type="hidden" name="init_default_person_uri" value="<?=htmlspecialchars($baseURL.'i')?>">

<p><input type="submit" name="save" value="Save changes"></p>
</form>
