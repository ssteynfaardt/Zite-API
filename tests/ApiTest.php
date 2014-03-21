<?php

require '../vendor/autoload.php';

use Ssteynfaardt\ZiteApi\Zite as Zite;

class ZiteTest extends PHPUnit_Framework_TestCase
{
	protected $config = null;
	protected $zite = null;

	protected function setUp()
	{
		$this->config = include 'config.php';
		$this->zite = new Zite($this->config);
		$this->zite->login($this->config['email'],$this->config['password']);
	}

	/**
	 * @expectedException Ssteynfaardt\ZiteApi\ZiteException
	 */
	public function testInvalidLogin(){
		$this->zite->login('myLoginEmail','myPass');
		$this->assertTrue($this->zite->error);
	}

	public function testValidLogin(){
		$auth = $this->zite->login($this->config['email'],$this->config['password']);
		$this->assertObjectHasAttribute('accessToken', $auth, "accessToken not found");
		$this->assertObjectHasAttribute('userId', $auth, "userId not found");
		$this->assertFalse($this->zite->error);
	}

	public function testCreateAccount(){
		$uniqId = uniqid();
		$account = $this->zite->createAccount("zite.test+{$uniqId}@gmail.com",'ZiteP4ss','John','Doe');
		$this->assertFalse($this->zite->error);
		$this->assertObjectHasAttribute('accessToken', $account, "accessToken not found");
		$this->assertObjectHasAttribute('userId', $account, "userId not found");
	}

	/**
	 * @depends testValidLogin
	 */
	public function testBookmarks(){
		$bookmarks = $this->zite->getBookmarks();
		$this->assertObjectHasAttribute('topics', $bookmarks, "topics not found");
		$this->assertFalse($this->zite->error);
	}

	/**
	 * @depends testBookmarks
	 */
	public function testBookmarkAdd(){
		$this->zite->addBookmark('mac');
		$this->assertFalse($this->zite->error);
		$bookmark = $this->zite->getArticles('mac');
		$this->assertTrue($bookmark->topic->bookmarked);
	}

	/**
	 * @depends testBookmarks
	 */
	public function testRemoveBookmark(){
		$this->zite->removeBookmark('mac');
		$this->assertFalse($this->zite->error);
		$bookmark = $this->zite->getArticles('mac');
		$this->assertFalse($bookmark->topic->bookmarked);

	}

	public function testLikeTopic(){
		$this->zite->likeTopic('mac');
		$this->assertFalse($this->zite->error);
		$bookmark = $this->zite->getArticles('mac');
		$this->assertTrue($bookmark->topic->liked);
	}

	public function testUnlikeTopic(){
		$this->zite->unlikeTopic('mac');
		$this->assertFalse($this->zite->error);
		$bookmark = $this->zite->getArticles('mac');
		$this->assertFalse($bookmark->topic->liked);
	}

	public function testExplore(){
		$explore = $this->zite->explore();
		$this->assertFalse($this->zite->error);
		$this->assertObjectHasAttribute('categories', $explore, "categories not found");
	}

	public function testSearch(){
		$search = $this->zite->search('mac');
		$this->assertFalse($this->zite->error);
		$this->assertObjectHasAttribute('topics', $search, "categories not found");
	}

	/**
	 * @depends testValidLogin
	 */
	public function testTopStories()
	{
		$topStories = $this->zite->getTopStories();
		$this->assertObjectHasAttribute('documents', $topStories, "documents not found");
		$this->assertFalse($this->zite->error);
	}

	/**
	 * @depends testValidLogin
	 */
	public function testArticles()
	{
		$articles = $this->zite->getArticles('mac');
		$this->assertObjectHasAttribute('documents', $articles, "documents not found");
		$this->assertFalse($this->zite->error);
	}

	/**
	 * @depends testTopStories
	 */
	public function testArticleMarkAsRead(){
		$topStories = $this->zite->getTopStories();
		$this->zite->markAsRead($topStories->documents[0]->url);
		$this->assertFalse($this->zite->error);
	}

	/**
	 * @depends testValidLogin
	 */
	public function testArticleLike()
	{
		$this->zite->likeArticle('http://www.zite.com/');
		$this->assertFalse($this->zite->error);
	}

	/**
	 * @depends testValidLogin
	 */
	public function testDislikeArticle()
	{
		$this->zite->dislikeArticle('http://www.zite.com/');
		$this->assertFalse($this->zite->error);
	}

	/**
	 * @depends testValidLogin
	 */
	public function testRemoveArticleLike()
	{
		$this->zite->removeArticleLike('http://www.zite.com/');
		$this->assertFalse($this->zite->error);
	}

	public function testSetPreferences()
	{
		$this->zite->setPreferences(1);
		$this->assertFalse($this->zite->error);
	}

	public function testGetProfile()
	{
		$profile = $this->zite->getProfile();
		$this->assertObjectHasAttribute('profile', $profile, "profile not found");
		$this->assertFalse($this->zite->error);
	}

	public function testHistory(){
		$history = $this->zite->getHistory();
		$this->assertObjectHasAttribute('allrecent', $history, "allrecent not found");
		$this->assertFalse($this->zite->error);
	}


}