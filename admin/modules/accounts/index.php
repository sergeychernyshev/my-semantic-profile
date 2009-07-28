<?
class AccountsModule extends EditModule
{
	function getName()
	{
		return "Accounts";
	}

	function getSlug()
	{
		return "accounts";
	}

	function displayForm($model, $personURI, $language)
	{
		global $foaf, $dc, $rdfs;

		?>
		<div id="<?=$this->getSlug()?>">
<?
		$query = '
		PREFIX foaf: <'.$foaf.'>
		PREFIX dc: <'.$dc.'>
		select ?servicehomepage, ?accountname, ?profilehomepage, ?accounttitle
		where {
		<'.$personURI->getURI().'> foaf:holdsAccount ?a .
		?a foaf:accountServiceHomepage ?servicehomepage .
		?a foaf:accountName ?accountname .
		OPTIONAL { ?a foaf:accountProfilePage ?profilehomepage } .
		OPTIONAL { ?a dc:title ?accounttitle }
		}';
		#echo "$query\n";
		$accounts = $model->sparqlQuery($query);

		if ($accounts)
		{
			foreach ($accounts as $account)
			{
				$key = $account['?servicehomepage']->getURI() . "\t" . $account['?accountname']->getLabel();
				$accountstoedit[$key] = array(
					'servicehomepage' => $account['?servicehomepage']->getURI(),
					'accountname' => $account['?accountname']->getLabel(),
					'profilehomepage' => $account['?profilehomepage']
						? $account['?profilehomepage']->getURI() : '',
 					'accounttitle' => ''
				);
			}

			foreach ($accounts as $account)
			{
				if ($account['?accounttitle']
					&& getLiteralLanguage($account['?accounttitle']) == $language)
				{
					$key = $account['?servicehomepage']->getURI() . "\t" . $account['?accountname']->getLabel();
					$accountstoedit[$key]['accounttitle']
						= $account['?accounttitle']->getLabel();
				}
			}

			foreach (array_values($accountstoedit) as $account)
			{
?>
				<div><table style="border: 1px solid #3F4C6B; padding: 5px; margin-bottom: 10px">
				<tr><td>Account title:</td><td><input type="text" name="<?=$this->getSlug()?>_accountTitle[]" value="<?=htmlspecialchars($account['accounttitle'])?>" size="60"/></td></tr>
				<tr><td>Service Homepage URL:</td><td><input type="text" name="<?=$this->getSlug()?>_homepageURL[]" value="<?=htmlspecialchars($account['servicehomepage'])?>" size="60"/></td></tr>
				<tr><td>Account name:</td><td><input type="text" name="<?=$this->getSlug()?>_accountName[]" value="<?=htmlspecialchars($account['accountname'])?>" size="40"/></td></tr>
				<tr><td>Profile URL:</td><td><input type="text" name="<?=$this->getSlug()?>_profileURL[]" value="<?=htmlspecialchars($account['profilehomepage'])?>" size="60"/></td></tr>
				</table></div>
<?
			}
		}
?>
		<div class="new_entries_accounts">
		<div><table style="border: 1px solid #3F4C6B; padding: 5px;">
		<tr><td>Account title:</td><td><input type="text" name="<?=$this->getSlug()?>_accountTitle[]" value="" size="60"/></td></tr>
		<tr><td>Service Homepage URL:</td><td><input type="text" name="<?=$this->getSlug()?>_homepageURL[]" value="" size="60"/></td></tr>
		<tr><td>Account name:</td><td><input type="text" name="<?=$this->getSlug()?>_accountName[]" value="" size="40"/></td></tr>
		<tr><td>Profile URL:</td><td><input type="text" name="<?=$this->getSlug()?>_profileURL[]" value="" size="60"/></td></tr>
		</table></div>
		</div>

		</div>
<?
	}

	function saveChanges($model, &$personURI, $language)
	{
		global $foaf, $dc, $rdfs;

		/*
		 * Accounts 
		 */
		$new_accounts = array();

		$new_titles = $_REQUEST[$this->getSlug().'_accountTitle'];
		$new_homepageurls = $_REQUEST[$this->getSlug().'_homepageURL'];
		$new_names = $_REQUEST[$this->getSlug().'_accountName'];
		$new_profileurls = $_REQUEST[$this->getSlug().'_profileURL'];

		// making sense of submitted data
		foreach($new_homepageurls as $homepageurl)
		{
			$name = array_shift($new_names);
			$title = array_shift($new_titles);
			$profileurl = array_shift($new_profileurls);

			// we're considering service homepage + account name to be unique identifier
			// both must be non-empty
			if ($homepageurl == '' || $name == '')
			{
				continue;
			}

			$key = $homepageurl . "\t" . $name;

			// all entries go always in same sequence in input arrays
			$new_accounts[$key]['homepageurl'] = $homepageurl;
			$new_accounts[$key]['name'] = $name;
			$new_accounts[$key]['title'][$language] = $title;
			$new_accounts[$key]['profileurl'] = $profileurl;
		}

		// we need to preserve titles in other languages for those accounts that were submitted
		// so we collect all titles before deleting all information about all accounts
		$query = '
		PREFIX foaf: <'.$foaf.'>
		PREFIX dc: <'.$dc.'>
		select ?servicehomepage, ?accountname, ?accounttitle
		where {
		<'.$personURI->getURI().'> foaf:holdsAccount ?a .
		?a foaf:accountServiceHomepage ?servicehomepage .
		?a foaf:accountName ?accountname .
		OPTIONAL { ?a dc:title ?accounttitle }
		}';
		#echo "$query\n";
		$accounts = $model->sparqlQuery($query);

		if ($accounts)
		{
			foreach ($accounts as $account)
			{
				$title_lang = getLiteralLanguage($account['?accounttitle']);

				// if this is not the language we edited, add this label too
				if ($account['?accounttitle'] != '' && $language != $title_lang)
				{
					$key = $account['?servicehomepage']->getURI() . "\t" . $account['?accountname']->getLabel();
					if (array_key_exists($key, $new_accounts))
					{
						$new_accounts[$key]['title'][$title_lang] =
							$account['?accounttitle']->getLabel();
					}
				}
			}
		}

		// now deleting all accounts and any statements associated with them 
		$it = $model->findAsIterator($personURI, new Resource($foaf.'holdsAccount'), NULL);
		while ($it->hasNext())
		{
			$statement = $it->next();
			$it2 = $model->findAsIterator($statement->getObject(), NULL, NULL);
			while ($it2->hasNext())
			{
				$model->remove($it2->next());
			}

			/*
			 * Remove account
			 */
			$model->remove($statement);
		}

		$holdsResource = new Resource($foaf.'holdsAccount');
		$homepageResource = new Resource($foaf.'accountServiceHomepage');
		$nameResource = new Resource($foaf.'accountName');
		$profileResource = new Resource($foaf.'accountProfilePage');
		$dcTitleResource= new Resource($dc.'title');

		foreach ($new_accounts as $key => $new_account)
		{
			$accountResource = new BlankNode($model);

			$model->add(new Statement($personURI, $holdsResource, $accountResource));
			$model->add(new Statement($accountResource,
				$homepageResource,
				new Resource($new_account['homepageurl'])
			));

			$model->add(new Statement($accountResource,
				$nameResource,
				new Literal($new_account['name'])
			));

			if ($new_account['profileurl'] != '')
			{
				$model->add(new Statement($accountResource,
					$profileResource,
					new Resource($new_account['profileurl'])
				));
			}

			// now let's collect all titles from previous version and insert them back with new one
			foreach ($new_account['title'] as $titlelanguage => $accounttitle)
			{
				if ($accounttitle != '')
				{
					$model->add(new Statement($accountResource, new Resource($dc.'title'),
						new Literal($accounttitle, $titlelanguage)));
				}
			}
		}

		return true;
	}
}

$modules[] = new AccountsModule();
