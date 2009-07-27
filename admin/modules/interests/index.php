<?
class InterestsModule extends EditModule
{
	function getName()
	{
		return "Interests";
	}

	function getSlug()
	{
		return "interests";
	}

	function displayForm($model, $personURI, $language)
	{
		global $foaf, $dc, $rdfs;

		?>
		<h2>Interests</h2>
		<div id="<?=$this->getSlug()?>">
<?
		$query = '
		PREFIX foaf: <'.$foaf.'>
		PREFIX dc: <'.$dc.'>
		select ?interest, ?interesttitle
		where {
		<'.$personURI->getURI().'> foaf:interest ?interest .
		OPTIONAL { ?interest dc:title ?interesttitle }
		}';
		#echo "$query\n";
		$interests = $model->sparqlQuery($query);

		if ($interests)
		{
			foreach ($interests as $interest)
			{
				$intereststoedit[$interest['?interest']->getURI()] = '';
			}

			foreach ($interests as $interest)
			{
				if ($interest['?interesttitle']
					&& getLiteralLanguage($interest['?interesttitle']) == $language)
				{
					$intereststoedit[$interest['?interest']->getURI()] = $interest['?interesttitle']->getLabel();
				}
			}

			foreach ($intereststoedit as $url => $title)
			{
?>
				<div>
				Title: <input type="text" name="<?=$this->getSlug()?>_interestTitle[]" value="<?=htmlspecialchars($title)?>" size="40"/>
				Page URL: <input type="text" name="<?=$this->getSlug()?>_interestURL[]" value="<?=htmlspecialchars($url)?>" size="60"/>
				</div>
<?
			}
		}
?>
		<div class="new_entries_interests">
		<div>
		Title: <input type="text" name="<?=$this->getSlug()?>_interestTitle[]" value="" size="40"/>
		Page URL: <input type="text" name="<?=$this->getSlug()?>_interestURL[]" value="" size="60"/>
		</div>
		</div>

		</div>
<?
	}

	function saveChanges($model, &$personURI, $language)
	{
		global $foaf, $dc, $rdfs;

		/*
		 * Interests 
		 */
		$new_interests = array();
		$new_interesturls = $_REQUEST[$this->getSlug().'_interestURL'];
		$new_interesttitles = $_REQUEST[$this->getSlug().'_interestTitle'];

		foreach($new_interesturls as $interesturl)
		{
			if ($interesturl == '')
			{
				array_shift($new_interesttitles);
				continue;
			}

			$new_interests[$interesturl][$language] = array_shift($new_interesttitles); // they are always in pairs
		}

		$it = $model->findAsIterator($personURI, new Resource($foaf.'interest'), NULL);
		while ($it->hasNext())
		{
			$intereststatements[] = $it->next();
		}

		foreach ($intereststatements as $statement)
		{
			$interesturl = $statement->getObject()->getURI();

			/*
			 * If this page was among submitted pages, we at least need to preserve titles in other languages
			 */
			$preservetitles = array_key_exists($interesturl, $new_interests);

			$interesttitlestatements = array();

			$it2 = $model->findAsIterator($statement->getObject(), new Resource($dc.'title'), NULL);
			while ($it2->hasNext())
			{
				$interesttitlestatements[] = $it2->next();
			}

			/*
			 * Remove titles
			 */
			foreach ($interesttitlestatements as $titlestatement)
			{
				if ($preservetitles)
				{
					$title = $titlestatement->getObject();
					/*
					 * Only setting if in different language
					 */
					if (getLiteralLanguage($title) != $language)
					{
						$new_interests[$interesturl][getLiteralLanguage($title)] = $title->getLabel();
					}
				}

				$model->remove($titlestatement);
			}

			/*
			 * Remove interest
			 */
			$model->remove($statement);
		}

		foreach ($new_interests as $new_interesturl => $new_interest)
		{
			$interestResource = new Resource($new_interesturl);

			$model->add(new Statement($personURI, new Resource($foaf.'interest'), $interestResource));

			foreach ($new_interest as $titlelanguage => $interesttitle)
			{
				if ($interesttitle != '')
				{
					$model->add(new Statement($interestResource, new Resource($dc.'title'), new Literal($interesttitle, $titlelanguage)));
				}
			}
		}

		return true;
	}
}

$modules[] = new InterestsModule();
