<?
class PeopleDisplayModule extends DisplayModule
{
	function getName()
	{
		return "People";
	}

	function getSlug()
	{
		return "people";
	}

	function displayContent($model, $personURI, $language)
	{
		global $profilefulluri, $defaultlang, $foaf, $dc, $rdfs;

		/*
		 * People person knows
		 */
		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
		select ?name, ?homepage, ?uri
		where {
		<'.$personURI->getURI().'> foaf:knows ?uri .
		OPTIONAL { ?uri  foaf:homepage  ?homepage } .
		OPTIONAL { ?uri  foaf:name ?name }
		}';
		#echo "$query\n";
		$people = $model->sparqlQuery($query);
		#echo var_export($people);

		if ($people)
		{
			foreach ($people as $person)
			{
				$pURI = $person['?uri']->getURI();

				$peopletodisplay[$pURI]['uri'] = $person['?uri'];
				$peopletodisplay[$pURI]['names'] = array();
				$peopletodisplay[$pURI]['homepages'] = array();
			}

			foreach ($people as $person)
			{
				$pURI = $person['?uri']->getURI();

				if ($person['?name'])
				{
					$peopletodisplay[$pURI]['names'][getLiteralLanguage($person['?name'])]
						= $person['?name']->getLabel();
				}

				if ($person['?homepage'])
				{
					$peopletodisplay[$pURI]['homepages'][] = $person['?homepage']->getURI();
				}
			}
		}

		if ($people && count($peopletodisplay))
		{
		?>
		<h2>People</h2>
		<div id="people"><ul>
		<?
			foreach ($peopletodisplay as $uri => $person)
			{
				if (is_a($person['uri'], 'BlankNode')
					&& count($person['homepages']) == 0
					&& count($person['names']) == 0
					)
				{
					continue;
				}

				?><li rel="foaf:knows" resource="<?=$profilefulluri->resolve($uri)->getURL() ?>">
		<?
				if (count($person['homepages']) > 0)
				{
					?><span rel="foaf:homepage" resource="<?=$profilefulluri->resolve($person['homepages'][0])->getURL() ?>"/><a rel="contact" href="<?=$profilefulluri->resolve($person['homepages'][0])->getURL() ?>"><?
				}

				if (array_key_exists($lang, $person['names']))
				{
					?><span property="foaf:name"<?=xmlLang($lang) ?> about="<?=$profilefulluri->resolve($uri)->getURL() ?>"><?=$person['names'][$lang] ?></span><?
				}
				elseif (array_key_exists($defaultlang, $person['names']))
				{
					?><span property="foaf:name"<?=xmlLang($defaultlang) ?> about="<?=$profilefulluri->resolve($uri)->getURL() ?>"><?=$person['names'][$defaultlang] ?></span><?
				}
				else
				{
					?><span><?=$uri ?></span><?
				}

				if (count($person['homepages']) > 0)
				{
					?></a><?
				}

				if (!is_a($person['uri'], 'BlankNode'))
				{
					?> <a href="<?=$profilefulluri->resolve($uri)->getURL() ?>" title="FOAF"><img src="foaf.png" alt="FOAF" style="border: 0px"/></a><?
				}
				?>
		</li><?
			}
		?></ul></div>
		<?
		}
		return true;
	}
}

$display_modules[] = new PeopleDisplayModule();
