<?
namespace iMarc;

/**
 * Google Site Search
 *
 * A PHP interface to the Google Custom Search XML API
 *
 * Documentation for the Google Custom Search XML API
 * https://developers.google.com/custom-search/docs/xml_results
 *
 * Usage:
 *
 * $search = new iMarc\GoogleSiteSearch(MY_SITE_SEARCH_KEY);
 * $results = $search->search('Bananas');
 *
 * @copyright iMarc LLC 2013
 * @author Jeff Turcotte <jeff@imarc.net>
 *
 * @license MIT
**/
class GoogleSiteSearch
{
	/**
	 * The Google Site Search key
	 *
	 * @var string
	 **/
	protected $site_search_key;

	/**
	 * Constructor
	 *
	 * @param string key The Google Site Search key
	 * @return void
	 **/
	public function __construct($key)
	{
		$this->site_search_key = $key;
	}

	/**
	 * Perform a site search
	 *
	 * Returns an object with the following properties:
	 *
	 *   page
	 *   start
	 *   end
	 *   total_guess
	 *   has_more
	 *   suggestion
	 *   results
	 *
	 * @param string query The search query
	 * @param string page The page to return
	 * @param string per_page How many results to dispaly per page
	 * @return object
	 **/
	public function search($terms, $page=1, $per_page=15)
	{
		$params = array(
			'q' => $terms,
			'start' => (($page - 1) * $per_page),
			'num' => $per_page,
			'output' => 'xml_no_dtd',
			'client' => 'google-csbe',
			'cx' => $this->site_search_key,
			'ie' => 'utf8',
			'oe' => 'utf8'
		);

		$url = 'http://www.google.com/search?' . http_build_query($params);
		$source = file_get_contents($url);

		$document = new DomDocument();
		$document->loadXml($source);
		$xpath = new DOMXpath($document);
		$results = new stdClass();

		$results->page = $page;
		$results->start = $xpath->evaluate('string(//RES/@SN)');
		$results->end = $xpath->evaluate('string(//RES/@EN)');
		$results->total_guess = $xpath->evaluate('string(//RES/M)');
		$results->has_more = $xpath->evaluate('boolean(//NU)');
		$results->suggestion = NULL;
		$results->results = array();

		if ($suggestion = $xpath->evaluate('string(//Spelling/Suggestion)')) {
			$results->suggestion = $suggestion;
		}

		foreach ($xpath->query('//RES/R') as $result) {
			$results->results[] = (object) array(
				'title' => $xpath->evaluate('string(T)', $result),
				'excerpt' => $xpath->evaluate('string(S)', $result),
				'url' => $xpath->evaluate('string(U)', $result)
			);
		}
		
		return $results;
	}
}


