Google Custom Search 
==================

A PHP interface to the Google Custom Search JSON API. The JSON API allows for [free](https://cse.google.com/cse) and [paid](https://www.google.com/work/search/products/gss.html) users to take advantage of adding Google search results to your website.

Google's documentation for the Custom Search JSON API can be found here:
https://developers.google.com/custom-search/json-api/v1/overview

Credentials
-----

**Search Engine ID** - On the [Custom Search Engine Control Panel](http://www.google.com/cse/manage/all), create a new search engine for the site you would like to integrate. Once created, you can retrieve your Search Engine ID from the *Setup* tab.

**API Key** - If you're using the free option, visit the [Google Developers Console](https://console.developers.google.com) and create a project for your search engine. Within your project, you’ll need to enable the *Custom Search API* from the *APIs* tab. Finally, on the *Credentials* tab, you will need to create a Public API access key by selecting the *Create new Key* option and choosing *Server key*. The API Key will now be available.

If you're using a paid option, you can find your API key in the [Control Panel](http://www.google.com/cse/manage/all) in the *Business > XML & JSON* tab.

Usage
-----

```(php)
$search = new iMarc\GoogleCustomSearch(SEARCH_ENGINE_ID, API_KEY);
$results = $search->search('Apples');
```