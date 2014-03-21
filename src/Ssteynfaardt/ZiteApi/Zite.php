<?php

namespace Ssteynfaardt\ZiteApi;

class Zite extends ApiBase {

	public $returnObject = false;

	/**
	 * constructor
	 */
	public function __construct($options = array())
	{
		if(isset($options['throwExceptionOnError'])){
			$this->throwExceptionOnError = $options['throwExceptionOnError'];
		}

		if(isset($options['accessToken'])){
			$this->accessToken = $options['accessToken'];
		}

		if(isset($options['userId'])){
			$this->userId = $options['userId'];
		}

		if(isset($options['returnObject'])){
			$this->returnObject = $options['returnObject'];
		}

		$this->method = 'GET';
	}

	/*
	|--------------------------------------------------------------------------
	| Account
	|--------------------------------------------------------------------------
	*/

	/**
	 * Create a new Zite account
	 *
	 * @param string $email valid email address
	 * @param string $password password for zite
	 * @param string $first first name
	 * @param string $last last name
	 * @return mixed result from zite containing an accessToken and userId
	 */
	public function createAccount($email, $password, $first, $last){
		//first we create an account to get an accessToken and userID
		$this->setUrl(self::ZITE_URL_CREATE,compact('email','password'));
		$this->setMethod('post');
		$registerResponse = $this->request();

		if($this->error === false){
			$tokens = json_decode($registerResponse);
			$this->accessToken = $tokens->accessToken;
			$this->userId = $tokens->userId;

			//Now we save the user detail
			$this->setUrl('profiles/set',compact('email','password','first','last'));
			$this->setMethod('post');
			$this->request();
		}

		if($this->returnObject){
			return json_decode($registerResponse);
		}
		return $registerResponse;
	}

	/**
	 * Login to Zite
	 *
	 * @param string $email valid email address
	 * @param string $password password used for zite
	 * @return mixed response from zite containing an accessToken and userId
	 * @throws ZiteException
	 */
	public function login($email, $password){
		$this->setUrl(self::ZITE_URL_LOGIN,compact('email','password'));
		$this->setMethod('post');
		$response = $this->request();
		if($this->error === false){
			$loginResponse = json_decode($response);
			$this->accessToken = $loginResponse->accessToken;
			$this->userId = $loginResponse->userId;
		}

		if($this->returnObject){
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * Send a forgot password email.
	 * @param string $email email account used to sign into zite
	 * @return mixed
	 */
	public function forgotPassword($email){
		$this->setUrl('account/reset',compact('email'));
		$this->setMethod('post');
		return $this->call();
	}

	/**
	 * Set popular articles preferences
	 * @param int $module_populararticles 1 enable, 0 disable
	 * @return mixed
	 */
	public function setPreferences($module_populararticles){
		$this->setUrl('account/setprefs',compact('module_populararticles'));
		$this->setMethod('post');
		return $this->call();
	}

	/*
	|--------------------------------------------------------------------------
	| Bookmarks
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get a list of user bookmarks (quicklist menu)
	 * @return mixed JSON containing bookmark list
	 * @throws ZiteException
	 */
	public function getBookmarks(){
		$this->setUrl('topics/bookmarks/list');
		return $this->call();
	}

	/**
	 * Add a topic to the bookmark list
	 * @param string $topic Topic to add to bookmarks
	 * @return mixed Empty JSON object
	 * @throws ZiteException
	 */
	public function addBookmark($topic){
		$this->setUrl('topics/bookmarks/add/'.$topic);
		$this->setMethod('post');
		return $this->call();
	}

	/**
	 * Remove a topic from the bookmark list
	 * @param string $topic Topic to remove from bookmarks
	 * @return mixed Empty JSON object
	 * @throws ZiteException
	 */
	public function removeBookmark($topic){
		$this->setUrl('topics/bookmarks/remove', compact('topic'));
		$this->setMethod('post');
		return $this->call();
	}

	/*
	|--------------------------------------------------------------------------
	| Profile
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get your user profile
	 * @return mixed JSON Object containing user profile
	 */
	public function getProfile(){
		$this->setUrl('profiles/get');
		return $this->call();
	}

	/*
	|--------------------------------------------------------------------------
	| History
	|--------------------------------------------------------------------------
	*/

	/**
	 * Fetch a history of articles viewed
	 * @param int $limit Amount to fetch
	 * @return mixed JSON Object containing user profile
	 */
	public function getHistory($limit = 150){
		$this->setUrl('history/list/articles',compact('limit'));
		return $this->call();
	}

	/*
	|--------------------------------------------------------------------------
	| Topics
	|--------------------------------------------------------------------------
	*/

	/**
	 * Like a topic
	 * @param string $topic Topic to like
	 * @return mixed Empty JSON object
	 * @throws ZiteException
	 */
	public function likeTopic($topic){
		$this->setUrl('topics/likes/add/'.$topic);
		$this->setMethod('post');
		return $this->call();
	}

	/**
	 * Remove the like from a topic
	 * @param string $topic Topic to unlike
	 * @return mixed Empty JSON object
	 * @throws ZiteException
	 */
	public function unlikeTopic($topic){
		$this->setUrl('topics/likes/remove', compact('topic'));
		$this->setMethod('post');
		return $this->call();
	}

	/*
	|--------------------------------------------------------------------------
	| Search
	|--------------------------------------------------------------------------
	*/

	/**
	 * Explore Zite, get new topics
	 * @param bool $iscoldstart
	 * @return mixed JSON Object
	 * @throws ZiteException
	 */
	public function explore($iscoldstart = false){
		$this->setUrl('topics/explore', compact('iscoldstart'));
		return $this->call();
	}

	/**
	 * Search Zite for new topics
	 * @param string $query The query ro search for
	 * @return mixed JSON Object containing the search results
	 * @throws ZiteException
	 */
	public function search($query){
		$this->setUrl('topics/search', array('q' => $query));
		$this->setMethod('post');
		return $this->call();
	}

	/*
	|--------------------------------------------------------------------------
	| Articles
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get the Top Stories
	 * @return mixed JSON Object containing top stories
	 * @throws ZiteException
	 */
	public function getTopStories(){
		return $this->getArticles();
	}

	/**
	 * Get an article list for a section
	 * @param string $section Section name to fetch
	 * @return mixed JSON Object containing articles
	 * @throws ZiteException
	 */
	public function getArticles($section = null){
		$this->setUrl('news',$section === null ? array() : compact('section'));
		return $this->call();
	}

	/**
	 * View an article in the Zite reader
	 * @param $url Url to view in the reader
	 * @return mixed HTML containing the article
	 * @throws ZiteException
	 */
	public function viewArticleReader($url){
		if(empty($url)){
			throw new ZiteException('$url can not be empty');
		}
		$initialFontSize = 0;
		$this->setUrl('news/reader',compact('url','initialFontSize'));
		$this->setExpectedResponse(200);
		return $this->call();
	}

	/**
	 * Mark an article as read
	 * @param string $url URL to the article
	 * @param string $section Section where the URL article was viewed in
	 * @return mixed Empty JSON Object
	 * @throws ZiteException
	 */
	public function markAsRead($url,$section = 'topstories'){
		if(empty($url)){
			throw new ZiteException('$url can not be empty');
		}

		if(empty($section)){
			throw new ZiteException('$section can not be empty');
		}

		$param = array(
			'event' => 'ArticleView',
			'orientation' => 'portrait',
			'source' => 'section',
			'section' => $section,
			'url' => $url,
			'webmode' => false,
		);

		$this->setUrl('log/event',$param);
		$this->setMethod('post');
		$this->setExpectedResponse(204);
		return $this->call();
	}

	/**
	 * Like an article
	 * @param string $url URL of the article
	 * @return mixed Empty JSON object
	 * @throws ZiteException
	 */
	public function likeArticle($url){
		$this->setUrl('doc/thumbs/like',compact('url'));
		$this->setMethod('post');
		return $this->call();
	}

	/**
	 * Dislike an article
	 * @param string $url URL of the article
	 * @return mixed Empty JSON object
	 * @throws ZiteException
	 */
	public function dislikeArticle($url){
		$this->setUrl('doc/thumbs/dislike',compact('url'));
		$this->setMethod('post');
		return $this->call();
	}

	/**
	 * Remove the like or dislike from and article
	 * @param string $url URL of the article
	 * @return mixed Empty JSON object
	 * @throws ZiteException
	 */
	public function removeArticleLike($url){
		$this->setUrl('doc/thumbs/remove',compact('url'));
		$this->setMethod('post');
		return $this->call();
	}

	/*
	|--------------------------------------------------------------------------
	| Private / Internal methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Determines if an output returned needs to be a JSON string or Object
	 * @return mixed If $returnObject is true then JSON object else JSON string
	 * @throws ZiteException
	 */
	private function call(){
		$res = $this->request();
		if($this->returnObject){
			return json_decode($res);
		}

		return $res;
	}

}