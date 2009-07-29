<?
class BasicInfoDisplayModule extends DisplayModule
{
	function getName()
	{
		return "Basic info";
	}

	function getSlug()
	{
		return "basic";
	}

	function displayContent($model, $personURI, $lang)
	{
		global $profilefulluri, $defaultlang, $foaf, $dc, $rdfs;
		/**
		 * Let's get person's primary pictures (img) and show it's thumbnail if it exists or just resized it to 100 hight
		 */
		$query = 'PREFIX foaf: <'.$foaf.'>
		select ?image, ?thumbnail
		where {
		<'.$personURI->getURI().'> foaf:img ?image .
		OPTIONAL { ?image foaf:thumbnail ?thumbnail } 
		}';
		#echo "$query\n";
		$images = $model->sparqlQuery($query);

		if ($images)
		{
		?>
		<h2>Images</h2>
		<div id="images">
		<?
			foreach ($images as $image)
			{
				?><span rel="foaf:img" resource="<?=$profilefulluri->resolve($image['?image']->getURI())->getURL() ?>"><a rel="gallery1" class="photo" href="<?=$profilefulluri->resolve($image['?image']->getURI())->getURL() ?>" title="<?=($personName ? $personName->getLabel()."'s photo" : 'photo')?>"><img src="<?=$profilefulluri->resolve($image['?thumbnail'] ? $image['?thumbnail']->getURI() : $image['?image']->getURI())->getURL() ?>" class="thumbnail" alt="<?=($personName ? $personName->getLabel()."'s photo" : 'photo')?>" rev="foaf:thumbnail" resource="<?=$profilefulluri->resolve($image['?image']->getURI())->getURL() ?>"/></a></span>
		<?
			}
		?></div><?
		}

		/**
		 * Now let's prng person's links
		 */

		/*
		 * Homepages
		 */
		$query = 'PREFIX foaf: <'.$foaf.'>
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
				$homepagestodisplay[$homepage['?homepage']->getURI()] = array();
			}

			foreach ($homepages as $homepage)
			{
				if ($homepage['?homepagetitle'])
				{
					$homepagestodisplay[$homepage['?homepage']->getURI()][getLiteralLanguage($homepage['?homepagetitle'])] = $homepage['?homepagetitle']->getLabel();
				}
			}
		}

		if ($homepages && count($homepagestodisplay))
		{
			?><h2>Homepages</h2>
		<div id="homepages"><ul><?

			foreach ($homepagestodisplay as $homepage => $languages)
			{
				if (array_key_exists($lang, $languages))
				{
					?><li rel="foaf:homepage"><a class="url" rel="me" about="<?=$profilefulluri->resolve($homepage)->getURL() ?>" property="dc:title" href="<?=$profilefulluri->resolve($homepage)->getURL() ?>"<?=xmlLang($lang) ?>><?=$languages[$lang] ?></a></li>
			<?
				}
				elseif (array_key_exists($defaultlang, $languages))
				{
					?><li rel="foaf:homepage"><a class="url" rel="me" about="<?=$profilefulluri->resolve($homepage)->getURL() ?>" property="dc:title" href="<?=$profilefulluri->resolve($homepage)->getURL() ?>"<?=xmlLang($lang) ?>><?=$languages[$defaultlang] ?></a></li>
			<?
				}
				else	
				{
					?><li rel="foaf:homepage" href="<?=$profilefulluri->resolve($homepage)->getURL() ?>"><a class="url" rel="me" href="<?=$profilefulluri->resolve($homepage)->getURL() ?>"><?=$profilefulluri->resolve($homepage)->getURL() ?></a></li>
			<?
				}
			}

		?></ul></div>
		<?
		}

		/*
		 * Blogs
		 */
		$query = 'PREFIX foaf: <'.$foaf.'>
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
				$blogstodisplay[$blog['?blog']->getURI()] = array();
			}

			foreach ($blogs as $blog)
			{
				if ($blog['?blogtitle'])
				{
					$blogstodisplay[$blog['?blog']->getURI()][getLiteralLanguage($blog['?blogtitle'])] =
						$blog['?blogtitle']->getLabel();
				}
			}
		}

		if ($blogs && count($blogstodisplay))
		{
			?><h2>Blogs</h2>
			<div id="blogs"><ul><?

				foreach ($blogstodisplay as $blog => $languages)
				{
					if (array_key_exists($lang, $languages))
					{
						?><li rel="foaf:weblog"><a class="url" rel="me" about="<?=$profilefulluri->resolve($blog)->getURL() ?>" property="dc:title" href="<?=$profilefulluri->resolve($blog)->getURL()?>"<?=xmlLang($lang) ?>><?=$languages[$lang] ?></a></li>
				<?
					}
					elseif (array_key_exists($defaultlang, $languages))
					{
						?><li rel="foaf:weblog"><a class="url" rel="me" about="<?=$profilefulluri->resolve($blog)->getURL() ?>" property="dc:title" href="<?=$profilefulluri->resolve($blog)->getURL() ?>"<?=xmlLang($lang) ?>><?=$languages[$defaultlang] ?></a></li>
				<?
					}
					else	
					{
						?><li rel="foaf:weblog" href="<?=$profilefulluri->resolve($blog)->getURL() ?>"><a class="url" rel="me" href="<?=$profilefulluri->resolve($blog)->getURL() ?>"><?=$profilefulluri->resolve($blog)->getURL() ?></a></li>
				<?
				}
			
				
			}

		?></ul></div>
		<?
		}

		return true;
	}
}

$display_modules[] = new BasicInfoDisplayModule();
