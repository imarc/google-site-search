Google Site Search 
==================

A PHP interface to the Google Custom Search XML API. 
Google's documentation for the Custom Search XML API can be found here:
https://developers.google.com/custom-search/docs/xml\_results

Usage
-----

```(php)
$search = new iMarc\GoogleSiteSearch(MY_SITE_SEARCH_KEY);
$results = $search->search('Bananas');
```
