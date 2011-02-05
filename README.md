Scrapt
=======

A PHP library for making webscraping a bit easier.

-------------------------------------------

The Anatomy of a Scraping Job
------------------------------

# Network. HTTP client that does everything a browser would, sans-javascript. Optionally, use proxies for longer jobs.
# Crawling. Pull data from a collection of relevant pages on a given site.
# Scraping. Find a path or pattern of data to extract from the given page.
# Validation. Ensure what you've extracted is valid data and not misgrabbed.
# Transform. Combine, filter, or otherwise munge the data until it is ready for storage.
# Persistence. Save the data in a queryable data store. Most often a relational database.
# Vigilence. If the page is updated and the scraper no longer works, stop and send out an alert.
# Updates. Re-scrape, and save only new data.

Finding the good data. The easiest method of scraping data from a given page
is 


Idea: Proxy a connection through it and have it record everything into 
      executable code. 


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
- An Amazon scraper that does a fixed search at some interval, monitoring price
  changes.
- A client for Bank of America that allows quick queries on the command-line.
  (Apparently very difficult)
- A security scanner that bruteforces a page login. Hotmail, for example, and
  detects when it starts asking for a Captcha.
- A diff bot, which can track changes to a page over time.
- An contact finder, which can crawl an entire site looking for email, phone, 
  or address information.
- A "Scraping Template", which can be customized to scrape certain types of data.
  For example, a record on a view page with many key-value pairs, or a listing on
  a site like Google. 
- 




