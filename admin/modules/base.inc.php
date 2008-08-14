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
		global $foaf, $dc;

		/*
		 * Names
		 */

		$names = array();

		$namenumber = 0;

		?><h2>Names</h2>
		<div id="<?=$this->getSlug()?>_names">
<?
		$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$name = $statement->getObject();

			if (getLiteralLanguage($name) == $language)
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

		<h2>Sites</h2>
		<div id="<?=$this->getSlug()?>_sites">
		<h3>Home pages<h3>
		<div id="<?=$this->getSlug()?>_homepages">
<?
		$query = '
		PREFIX foaf: <'.$foaf.'>
		PREFIX dc: <'.$dc.'>
		select ?homepage, ?homepagetitle
		where {
		<'.$personURI->getURI().'> foaf:homepage ?homepage .
		OPTIONAL { ?homepage dc:title ?homepagetitle }
		}';
		#echo "$query\n";
		$homepages = $model->sparqlQuery($query);

		foreach ($homepages as $homepage)
        	{
			$homepagestoedit[$homepage['?homepage']->getURI()] = '';
		}

		foreach ($homepages as $homepage)
        	{
			if ($homepage['?homepagetitle']
				&& getLiteralLanguage($homepage['?homepagetitle']) == $language)
			{
				$homepagestoedit[$homepage['?homepage']->getURI()] = $homepage['?homepagetitle']->getLabel();
			}
		}

		foreach ($homepagestoedit as $url => $title)
		{
?>
			<div>
			Title: <input type="text" name="<?=$this->getSlug()?>_homepageTitle[]" value="<?=htmlspecialchars($title)?>" size="40"/>
			URL: <input type="text" name="<?=$this->getSlug()?>_homepageURL[]" value="<?=htmlspecialchars($url)?>" size="60"/>
			</div>
<?
		}
?>
		<div>
		Title: <input type="text" name="<?=$this->getSlug()?>_homepageTitle[]" value="" size="40"/>
		URL: <input type="text" name="<?=$this->getSlug()?>_homepageURL[]" value="" size="60"/>
		</div>

		</div>
		</div>
<?
	}

	function saveChanges($model, $personURI, $language)
	{
		global $foaf, $dc;

		/*
		 * Names
		 */
		$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
		while ($it->hasNext())
		{
			$namestatements[] = $it->next();
		}

		foreach ($namestatements as $statement)
		{
			if (getLiteralLanguage($statement->getObject()) == $language)
			{
				$model->remove($statement);
			}
		}

		$new_names = $_REQUEST[$this->getSlug().'_name'];
		foreach ($new_names as $name)
		{
			if ($name != '')
			{
				$model->add(new Statement($personURI, new Resource($foaf.'name'), new Literal($name, $language)));
			}
		}

		/*
		 * Homepages 
		 */
		$new_homepages = array();
		$new_homepageurls = $_REQUEST[$this->getSlug().'_homepageURL'];
		$new_homepagetitles = $_REQUEST[$this->getSlug().'_homepageTitle'];

		foreach($new_homepageurls as $homepageurl)
		{
			if ($homepageurl == '')
			{
				array_shift($new_homepagetitles);
				continue;
			}

			$new_homepages[$homepageurl][$language] = array_shift($new_homepagetitles); // they are always in pairs
		}

		$it = $model->findAsIterator($personURI, new Resource($foaf.'homepage'), NULL);
		while ($it->hasNext())
		{
			$homepagestatements[] = $it->next();
		}

		foreach ($homepagestatements as $statement)
		{
			$homepageurl = $statement->getObject()->getURI();

			/*
			 * If this page was among submitted pages, we at least need to preserve titles in other languages
			 */
			$preservetitles = array_key_exists($homepageurl, $new_homepages);

			$homepagetitlestatements = array();

			$it2 = $model->findAsIterator($statement->getObject(), new Resource($dc.'title'), NULL);
			while ($it2->hasNext())
			{
				$homepagetitlestatements[] = $it2->next();
			}

			/*
			 * Remove titles
			 */
			foreach ($homepagetitlestatements as $titlestatement)
			{
				if ($preservetitles)
				{
					$title = $titlestatement->getObject();
					/*
					 * Only setting if in different language
					 */
					if (getLiteralLanguage($title) != $language)
					{
						$new_homepages[$homepageurl][getLiteralLanguage($title)] = $title->getLabel();
					}
				}

				$model->remove($titlestatement);
			}

			/*
			 * Remove homepage
			 */
			$model->remove($statement);
		}

		foreach ($new_homepages as $new_homepageurl => $new_homepage)
		{
			$homepageResource = new Resource($new_homepageurl);

			$model->add(new Statement($personURI, new Resource($foaf.'homepage'), $homepageResource));

			foreach ($new_homepage as $titlelanguage => $homepagetitle)
			{
				if ($homepagetitle != '')
				{
					$model->add(new Statement($homepageResource, new Resource($dc.'title'), new Literal($homepagetitle, $titlelanguage)));
				}
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
