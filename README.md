Scrapt
=======

A PHP library for making webscraping a bit easier.

-------------------------------------------

The Anatomy of a Scraping Job
------------------------------

- Network. HTTP client that does everything a browser would, sans-javascript. Optionally, use proxies for longer jobs.
- Crawling. Pull data from a collection of relevant pages on a given site.
- Scraping. Find a path or pattern of data to extract from the given page.
- Validation. Ensure what you've extracted is valid data and not misgrabbed.
- Transform. Combine, filter, or otherwise munge the data until it is ready for storage.
- Persistence. Save the data in a queryable data store. Most often a relational database.
- Vigilence. If the page is updated and the scraper no longer works, stop and send out an alert.
- Updates. Re-scrape, and save only new data.

Example Scraper
---------------

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


Use Cases
----------

- A bank of nodes scraping yellowpages.com, clicking through proxies to avoid
  the banhammer. No page is pulled twice. Nodes know how much traffic has gone
  through a given proxy and can form sessions which look legitimate.
- An Overstock.com scraper that does a fixed search at some interval, monitoring price
  changes.
- A client for Bank of America that allows quick queries on the command-line.
  (Apparently pretty difficult)
- A diff bot, which can track changes to a page over time.
- A contact finder, which can crawl an entire site looking for email, phone, 
  or address information.
- A "Scraping Template", which can be customized to scrape certain types of data.
  For example, a record on a view page with many key-value pairs, or a listing on
  a site like Google.
