<?php
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
		global $foaf, $dc, $rdfs;

		/*
		 * URI
		 */
		?><h2>URI</h2>
		<div id="<?php echo $this->getSlug()?>_uri">
		<input type="text" name="<?php echo $this->getSlug()?>_uri" value="<?php echo htmlspecialchars($personURI->getURI())?>" size="60">
		</div>
<?php
		/*
		 * Names
		 */

		$names = array();

		$namenumber = 0;

		?><h2>Names</h2>
		<div id="<?php echo $this->getSlug()?>_names">
<?php
		$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$name = $statement->getObject();

			if (getLiteralLanguage($name) == $language)
			{
			?>
				<div><input type="text" name="<?php echo $this->getSlug()?>_name[]" value="<?php echo htmlspecialchars($name->getLabel())?>"/></div>
<?php
				$namenumber++;
			}
		}
?>
		<div class="new_entries_name">
		<div>
		<input type="text" name="<?php echo $this->getSlug()?>_name[]" value=""/>
		</div>
		</div>
		</div>

		<h2>OpenID</h2>
		<div id="<?php echo $this->getSlug()?>_openid">
<?php
		$it = $model->findAsIterator($personURI, new Resource($foaf.'openid'), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$openid = $statement->getObject();

			?><div><input type="text" name="<?php echo $this->getSlug()?>_openid[]" value="<?php echo htmlspecialchars($openid->getURI())?>" size="60"/></div><?php
		}
?>
		<div class="new_entries_openid">
		<div>
		<input type="text" name="<?php echo $this->getSlug()?>_openid[]" value="" size="60"/>
		</div>
		</div>
		</div>

		<h2>Sites</h2>
		<div id="<?php echo $this->getSlug()?>_sites">
		<h3>Home pages</h3>
		<div id="<?php echo $this->getSlug()?>_homepages">
<?php
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

		if ($homepages)
		{
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
				Title: <input type="text" name="<?php echo $this->getSlug()?>_homepageTitle[]" value="<?php echo htmlspecialchars($title)?>" size="40"/>
				URL: <input type="text" name="<?php echo $this->getSlug()?>_homepageURL[]" value="<?php echo htmlspecialchars($url)?>" size="60"/>
				</div>
<?php
			}
		}
?>
		<div class="new_entries_homepages">
		<div>
		Title: <input type="text" name="<?php echo $this->getSlug()?>_homepageTitle[]" value="" size="40"/>
		URL: <input type="text" name="<?php echo $this->getSlug()?>_homepageURL[]" value="" size="60"/>
		</div>
		</div>

		</div>
		<h3>Blogs</h3>
		<div id="<?php echo $this->getSlug()?>_blogs">
<?php
		$query = '
		PREFIX foaf: <'.$foaf.'>
		PREFIX dc: <'.$dc.'>
		select ?blog, ?blogtitle
		where {
		<'.$personURI->getURI().'> foaf:weblog ?blog .
		OPTIONAL { ?blog dc:title ?blogtitle }
		}';
		#echo "$query\n";
		$blogs = $model->sparqlQuery($query);

		if ($blogs)
		{
			foreach ($blogs as $blog)
        		{
				$blogstoedit[$blog['?blog']->getURI()] = '';
			}

			foreach ($blogs as $blog)
        		{
				if ($blog['?blogtitle']
					&& getLiteralLanguage($blog['?blogtitle']) == $language)
				{
					$blogstoedit[$blog['?blog']->getURI()] = $blog['?blogtitle']->getLabel();
				}
			}

			foreach ($blogstoedit as $url => $title)
			{
?>
			<div>
				Title: <input type="text" name="<?php echo $this->getSlug()?>_blogTitle[]" value="<?php echo htmlspecialchars($title)?>" size="40"/>
				URL: <input type="text" name="<?php echo $this->getSlug()?>_blogURL[]" value="<?php echo htmlspecialchars($url)?>" size="60"/>
				</div>
<?php
			}
		}
?>
		<div class="new_entries_blogs">
		<div>
		Title: <input type="text" name="<?php echo $this->getSlug()?>_blogTitle[]" value="" size="40"/>
		URL: <input type="text" name="<?php echo $this->getSlug()?>_blogURL[]" value="" size="60"/>
		</div>

		</div>
		</div>
		</div>

		<h2>Additional Resources</h2>
		Resources to be added as rdfs:seeAlso
		<div id="<?php echo $this->getSlug()?>_seealso">
<?php
		$it = $model->findAsIterator($personURI, new Resource($rdfs.'seeAlso'), NULL);
		while ($it->hasNext()) {
			$statement = $it->next();
			$seealso= $statement->getObject();

			?><div><input type="text" name="<?php echo $this->getSlug()?>_seealso[]" value="<?php echo htmlspecialchars($seealso->getURI())?>" size="60"/></div><?php
		}
?>
		<div class="new_entries_seealso">
		<div>
		<input type="text" name="<?php echo $this->getSlug()?>_seealso[]" value="" size="60"/>
		</div>
		</div>
		</div>
<?php
	}

	function saveChanges($model, &$personURI, $language)
	{
		global $foaf, $dc, $rdfs;

		/*
		 * URI
		 */
		$new_person_uri = $_REQUEST[$this->getSlug().'_uri'];
		if ($new_person_uri && $new_person_uri != $personURI)
		{
			$newPersonURI = new Resource($new_person_uri);
			$model->replace($personURI, null, $personURI, $newPersonURI);

			$personURI = $newPersonURI;	
		}

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
		 * OpenIDs
		 */
		$it = $model->findAsIterator($personURI, new Resource($foaf.'openid'), NULL);
		while ($it->hasNext())
		{
			$model->remove($it->next());
		}

		$new_openids = $_REQUEST[$this->getSlug().'_openid'];
		foreach ($new_openids as $openid)
		{
			if ($openid != '')
			{
				$model->add(new Statement($personURI, new Resource($foaf.'openid'), new Resource($openid)));
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

		/*
		 * Blogs 
		 */
		$new_blogs= array();
		$new_blogurls = $_REQUEST[$this->getSlug().'_blogURL'];
		$new_blogtitles = $_REQUEST[$this->getSlug().'_blogTitle'];

		foreach($new_blogurls as $blogurl)
		{
			if ($blogurl == '')
			{
				array_shift($new_blogtitles);
				continue;
			}

			$new_blogs[$blogurl][$language] = array_shift($new_blogtitles); // they are always in pairs
		}

		$it = $model->findAsIterator($personURI, new Resource($foaf.'weblog'), NULL);
		while ($it->hasNext())
		{
			$blogstatements[] = $it->next();
		}

		foreach ($blogstatements as $statement)
		{
			$blogurl = $statement->getObject()->getURI();

			/*
			 * If this blog was among submitted blogs, we at least need to preserve titles in other languages
			 */
			$preservetitles = array_key_exists($blogurl, $new_blogs);

			$blogtitlestatements = array();

			$it2 = $model->findAsIterator($statement->getObject(), new Resource($dc.'title'), NULL);
			while ($it2->hasNext())
			{
				$blogtitlestatements[] = $it2->next();
			}

			/*
			 * Remove titles
			 */
			foreach ($blogtitlestatements as $titlestatement)
			{
				if ($preservetitles)
				{
					$title = $titlestatement->getObject();
					/*
					 * Only setting if in different language
					 */
					if (getLiteralLanguage($title) != $language)
					{
						$new_blogs[$blogurl][getLiteralLanguage($title)] = $title->getLabel();
					}
				}

				$model->remove($titlestatement);
			}

			/*
			 * Remove blog 
			 */
			$model->remove($statement);
		}

		foreach ($new_blogs as $new_blogurl => $new_blog)
		{
			$blogResource = new Resource($new_blogurl);

			$model->add(new Statement($personURI, new Resource($foaf.'weblog'), $blogResource));

			foreach ($new_blog as $titlelanguage => $blogtitle)
			{
				if ($blogtitle != '')
				{
					$model->add(new Statement($blogResource, new Resource($dc.'title'), new Literal($blogtitle, $titlelanguage)));
				}
			}
		}

		/*
		 * seeAlso 
		 */
		$it = $model->findAsIterator($personURI, new Resource($rdfs.'seeAlso'), NULL);
		while ($it->hasNext())
		{
			$model->remove($it->next());
		}

		$new_seealso = $_REQUEST[$this->getSlug().'_seealso'];
		foreach ($new_seealso as $seealso)
		{
			if ($seealso != '')
			{
				$model->add(new Statement($personURI, new Resource($rdfs.'seeAlso'), new Resource($seealso)));
			}
		}

		return true;
	}
}

$modules[] = new BasicInfoModule();
