<?php
class PeopleModule extends EditModule
{
	function getName()
	{
		return "People";
	}

	function getSlug()
	{
		return "people";
	}

	function displayForm($model, $personURI, $language)
	{
		global $foaf, $dc;

		/*
		 * People
		 *
		 * OK, for people we display only URI and allow editing of URI, plus replacing nodeIDs with URIs
		 *
		 * TODO The rest of editing is hopefully going to be handled by passing person's URI or nodeID
		 * to the whole app as parameter instead of using default profile URI
		 */
?>
		<div id="<?php echo $this->getSlug()?>_people">
<?php
		$query = 'PREFIX foaf: <'.$foaf.'>
		select ?name, ?homepage, ?uri
		where {
		<'.$personURI->getURI().'> foaf:knows ?uri .
		OPTIONAL { ?uri  foaf:homepage  ?homepage } .
		OPTIONAL { ?uri  foaf:name ?name }
		}';
		#echo "$query\n";
		$people = $model->sparqlQuery($query);

		if ($people)
		{
			foreach ($people as $person)
			{
				$peopletoedit[$person['?uri']->getURI()] = array( 'resource' => $person['?uri']);
			}

			foreach ($people as $person)
			{
				if ($person['?name']
					&& getLiteralLanguage($person['?name']) == $language)
				{
					$peopletoedit[$person['?uri']->getURI()]['name'] = $person['?name']->getLabel();
				}

				if ($person['?homepage'])
				{
					$peopletoedit[$person['?uri']->getURI()]['homepage'] = $person['?homepage']->getURI();
				}
			}

			foreach ($peopletoedit as $uri => $person)
			{
?>
				<div>
				<input type="hidden" name="<?php echo $this->getSlug()?>_personNodeID[]" value="<?php if (is_a($person['resource'], 'BlankNode')) { echo htmlspecialchars($uri); }?>"/>
				<input type="hidden" name="<?php echo $this->getSlug()?>_personURI[]" value="<?php if (!is_a($person['resource'], 'BlankNode')) { echo htmlspecialchars($uri); }?>"/>
				URI: <input type="text" name="<?php echo $this->getSlug()?>_personNewURI[]" value="<?php if (!is_a($person['resource'], 'BlankNode')) { echo htmlspecialchars($uri); }?>" size="50"/>
	<?php
				if (array_key_exists('homepage', $person))
				{
					?><a href="<?php echo htmlspecialchars($person['homepage'])?>"><?php
				}

				if (array_key_exists('name', $person))
				{
					echo htmlspecialchars($person['name']);
				}
				else
				{
					?><i>Unnamed<?php

					if (is_a($person['resource'], 'BlankNode'))
					{
						?> (<?php echo htmlspecialchars($uri)?>)<?php
					}
					?></i><?php
				}

				if (array_key_exists('homepage', $person))
				{
					?></a><?php
				}

				if (!is_a($person['resource'], 'BlankNode'))
				{
				?>
				(<a href="?lang=<?php echo $language?>&personURI=<?php echo urlencode($person['resource']->getURI())?>" title="Will work in the future">edit</a>)
				<?php
				}

				?>
				</div>
<?php
			}
		}
?>
		<div class="new_entries_person">
		<div>
			<input type="hidden" name="<?php echo $this->getSlug()?>_personNodeID[]" value=""/>
			<input type="hidden" name="<?php echo $this->getSlug()?>_personURI[]" value=""/>
			URI: <input type="text" name="<?php echo $this->getSlug()?>_personNewURI[]" size="50"/>
		</div>
		</div>

		</div>
<?php
	}

	function saveChanges($model, $personURI, $language)
	{
		global $foaf, $dc;

		/*
		 * People
		 *
		 * TODO Deleting people
		 */
		$new_people_nodes = $_REQUEST[$this->getSlug().'_personNodeID'];
		$new_people_uris = $_REQUEST[$this->getSlug().'_personURI'];

		$new_people_new_uris = $_REQUEST[$this->getSlug().'_personNewURI'];

		foreach($new_people_nodes as $person_node)
		{
			$person_uri = array_shift($new_people_uris);
			$person_new_uri = array_shift($new_people_new_uris);

			if ($person_node != '' && $person_new_uri !='')
			{
				/*
				 * Replacing nodeID with URI
				 */
				$existing_node = new BlankNode($person_node);
				$replacement = new Resource($person_new_uri);

				$model->replace($existing_node, null, $existing_node, $replacement);
			}
			elseif ($person_uri != '' && $person_new_uri !='' && $person_uri != $person_new_uri)
			{
				/*
				 * Changing URI
				 */
				$existing_node = new Resource($person_uri);
				$replacement = new Resource($person_new_uri);


				$model->replace($existing_node, null, $existing_node, $replacement);
			}
			elseif ($person_uri == '' and $person_node == '' and $person_new_uri !='')
			{
				/*
				 * New Person 
				 */
				$model->add(new Statement($personURI, new Resource($foaf.'knows'), new Resource($person_new_uri)));
			}
		}

#		exit;

		return true;
	}
}

$modules[] = new PeopleModule();
