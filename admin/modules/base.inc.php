<?

$modules = array(
	new BasicInfoModule(),
	new GeoLocationModule()
);

abstract class EditModule
{
	abstract function getSlug();

	abstract function getName();

	function displayForm($model, $personURI, $language)
	{

	}

	function saveChanges($model, $personURI, $language)
	{
		return true;
	}
}

class BasicInfoModule extends EditModule
{
	function getName()
	{
		return "Basic info";
	}

	function getSlug()
	{
		return "basic";
	}

	function displayForm($model, $personURI, $language)
	{
		global $foaf;

		$names = array();

		$namenumber = 0;

		?><h2>Names</h2>
		<div id="<?=$this->getSlug()?>_names">
<?

		$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$name = $statement->getObject();

			if ($name->getLanguage() == $language || ($name->getLanguage() == '' && $language == 'en'))
			{
			?>
				<div><input type="text" name="<?=$this->getSlug()?>_name[]" value="<?=htmlspecialchars($name->getLabel())?>"/></div>
<?
				$namenumber++;
			}
		}
?>
		<div>
		<input type="text" name="<?=$this->getSlug()?>_name[]" value=""/>
		</div>

		</div>
<?
	}

	function saveChanges($model, $personURI, $language)
	{
		global $foaf;

		$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
		while ($it->hasNext())
		{
			$namestatements[] = $it->next();
		}

		foreach ($namestatements as $statement)
		{
			$model->remove($statement);
		}

		$new_names = $_REQUEST[$this->getSlug().'_name'];
		foreach ($new_names as $name)
		{
			if ($name != '')
			{
				$model->add(new Statement($personURI, new Resource($foaf.'name'), new Literal($name, $language)));
			}
		}

		return true;
	}
}

class GeoLocationModule extends EditModule
{
	function getName()
	{
		return "Location";
	}

	function getSlug()
	{
		return "geo";
	}
}
