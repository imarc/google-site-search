<?php
namespace iMarc;

/**
 * Google Custom Search
 *
 * A PHP interface to the Google Custom Search JSON API
 *
 * Documentation for the Google Custom Search JSON API
 * https://developers.google.com/custom-search/json-api/v1/overview
 *
 * Usage:
 *
 * $search = new iMarc\GoogleCustomSearch(SEARCH_ENGINE_ID, API_KEY);
 * $results = $search->search('Apples');
 *
 * @copyright iMarc LLC 2015
 * @author Jeff Turcotte <jeff@imarc.net>
 * @author Dan Collins <dan@imarc.net>
 * @version 2.0.0
 *
 * @license MIT
**/
class GoogleCustomSearch
{
    /**
     * Google Search Engine ID
     *
     * @var string
     **/
    protected $search_engine_id;

    /**
     * Google API Key
     *
     * @var string
     **/
    protected $api_key;

    /**
     * Constructor
     *
     * @param string search_engine_id Search Engine ID
     * @param string api_key API Key
     * @return void
     **/
    public function __construct($search_engine_id, $api_key)
    {
        $this->search_engine_id = $search_engine_id;
        $this->api_key = $api_key;
    }

    /**
     * Sends search request to Google
     *
     * @param array params The parameters of the search request
     * @return object The raw results of the search request
     **/
    private function request($params)
    {
        $params = array_merge(
            $params,
            [
                'key' => $this->api_key,
                'cx' => $this->search_engine_id
            ]
        );

        // disable peer verification
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        // use cURL if avaible, otherwise fallback to file_get_contents
        if (function_exists('curl_version')) {
            $response = $this->getSslPage('https://www.googleapis.com/customsearch/v1?' . http_build_query($params));
        } else {
            $response = file_get_contents('https://www.googleapis.com/customsearch/v1?' . http_build_query($params), false, $context);
        }

        return json_decode($response);
    }

    /**
     * Perform search
     *
     * Returns an object with the following properties:
     *
     *   page
     *   perPage
     *   start
     *   end
     *   totalResults
     *   results
     *     title
     *     snippet
     *     htmlSnippet
     *     link
     *     image
     *     thumbnail
     *
     * @param string terms The search terms
     * @param integer page The page to return
     * @param integer per_page How many results to dispaly per page
     * @param array extra Extra parameters to pass to Google
     * @return object The results of the search
     * @throws Exception If error is returned from Google
     **/
    public function search($terms, $page=1, $per_page=10, $extra=[])
    {
        // Google only allows 10 results at a time
        $per_page = ($per_page > 10) ? 10 : $per_page;

        $params = [
            'q' => $terms,
            'start' => (($page - 1) * $per_page) + 1,
            'num' => $per_page
        ];
        if (sizeof($extra)) {
            $params = array_merge($params, $extra);
        }

        $response = $this->request($params);

        if (isset($response->error)) {
            throw new \Exception($response->error->message);
        }

        $request_info = $response->queries->request[0];

        $results = new \stdClass();
        $results->page = $page;
        $results->perPage = $per_page;
        $results->start = $request_info->startIndex;
        $results->end = ($request_info->startIndex + $request_info->count) - 1;
        $results->totalResults = $request_info->totalResults;
        $results->results = [];

        if (isset($response->items)) {
            foreach ($response->items as $result) {
                $results->results[] = (object) [
                    'title' => $result->title,
                    'snippet' => $result->snippet,
                    'htmlSnippet' => $result->htmlSnippet,
                    'link' => $result->link,
                    'image' => isset($result->pagemap->cse_image) ? $result->pagemap->cse_image[0]->src : '',
                    'thumbnail' => isset($result->pagemap->cse_thumbnail) ? $result->pagemap->cse_thumbnail[0]->src : '',
                ];
            }
        }

        return $results;
    }

    /**
     * Allow call to api under https using cURL - replacement function for file_get_contents
     * By setting CURLOPT_RETURNTRANSFER to true we're able to fetch the results via cURL
     * @param string $url
     * @return Object $results
     */
    public function getSslPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
