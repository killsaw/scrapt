<?php

require './Scrapt.php';

function scrape_delicious()
{
	$scraper = new Scrapt;
	$scraper->setURL('http://www.delicious.com/');
	$data = $scraper->cache();
	$page = $scraper->getPage();
	
	$links = $page->findBySelector('#bookmarklist li');
	$marks = array();
	
	foreach($links as $l) {
		$link = $l->find('.data h4>a', 0);
		
		if ($link instanceof simple_html_dom_node) {
			$url = $link->href;
			$name = $link->innertext;
			$faves = intval($l->find('span.delNavCount', 0)->innertext);
			$tweets = intval($l->find('h5.num-tweets', 0)->innertext);
			
			$marks[] = array(
						'url'=>$url,
						'name'=>$name,
						'faves'=>$faves,
						'tweets'=>$tweets
					   );
		}
	}
	return $marks;
}

$links = scrape_delicious();
print_r($links);