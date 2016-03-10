<?php
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

	function displayContent($model, $personURI, $lang)
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
		<?php
			foreach ($peopletodisplay as $uri => $person)
			{
				if (is_a($person['uri'], 'BlankNode')
					&& count($person['homepages']) == 0
					&& count($person['names']) == 0
					)
				{
					continue;
				}

				?><li rel="foaf:knows" resource="<?php echo $profilefulluri->resolve($uri)->getURL() ?>">
		<?php
				if (count($person['homepages']) > 0)
				{
					?><span rel="foaf:homepage" resource="<?php echo $profilefulluri->resolve($person['homepages'][0])->getURL() ?>"/><a rel="contact" href="<?php echo $profilefulluri->resolve($person['homepages'][0])->getURL() ?>"><?php
				}

				if (array_key_exists($lang, $person['names']))
				{
					?><span property="foaf:name"<?php echo xmlLang($lang) ?> about="<?php echo $profilefulluri->resolve($uri)->getURL() ?>"><?php echo $person['names'][$lang] ?></span><?php
				}
				elseif (array_key_exists($defaultlang, $person['names']))
				{
					?><span property="foaf:name"<?php echo xmlLang($defaultlang) ?> about="<?php echo $profilefulluri->resolve($uri)->getURL() ?>"><?php echo $person['names'][$defaultlang] ?></span><?php
				}
				else
				{
					?><span><?php echo $uri ?></span><?php
				}

				if (count($person['homepages']) > 0)
				{
					?></a><?php
				}

				if (!is_a($person['uri'], 'BlankNode'))
				{
					?> <a href="<?php echo $profilefulluri->resolve($uri)->getURL() ?>" title="FOAF"><img src="foaf.png" alt="FOAF" style="border: 0px"/></a><?php
				}
				?>
		</li><?php
			}
		?></ul></div>
		<?php
		}
		return true;
	}
}

$display_modules[] = new PeopleDisplayModule();
