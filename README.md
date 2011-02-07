#Scrapt
A PHP library for making webscraping a bit easier.

##Example Scraper


	$page = Scrapt::get('https://somepbxcompany.com/');
	if ($page->contains('Please log in to access this page')) {
		$form = $page->getForm();
		$form->uemail = PBX_PORTAL_USERNAME; // is url encoded
		$form->pwd=PBX_PORTAL_PASSWORD;
		$page = Scrapt::submit($form);
	}
	$report_url = 'https://somepbxcompany.com/report.php';
	$report_vars = array(
		'date'=>'2011-02-01'
	);
	$page = Scrapt::get($report_url, $report_vars);

Design Goals
------------

- By default, look like a human browser.
- Support Mechanize-style page access.
- Manage page caches intelligently. Prevent server clobbering.


Future Ideas
------------

- On the Mac, integrate phpOSA to remote Fake.app.
- On other platforms, remote Selenium
- Support proxy banks.
- Support multi-node scraping, with coordination and job distribution.
- Allow a scraping session to be paused without having to start over.

