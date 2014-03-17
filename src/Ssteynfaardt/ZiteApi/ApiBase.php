<?php

namespace Ssteynfaardt\ZiteApi;

class ApiBase {
	public      $accessToken = null;
	public      $userId = null;
	protected   $throwExceptionOnError = true;
	private     $serverUrl = 'https://api.zite.com/api';
	private     $apiVersion = 'v2';
	private     $appVersion = '2.0';
	private     $deviceType = 'ipad';
	private     $validMethods = array('GET','POST');
	private     $url = null;
	const       ZITE_URL_LOGIN = 'account/login';
	const       ZITE_URL_CREATE = 'account/create';

	/**
	 * Set the URL for the CURL request
	 * @param $endpoint REST endpoint of the ZITE API
	 * @param array $reqParams Parmas that needs to be passed to the API
	 */
	protected  function setUrl($endpoint, $reqParams = array()){
		$defaultParams = array('appver'=> $this->appVersion, 'deviceType' => $this->deviceType);
		//don't include the accessToken with account pages
		$endpoints = array(self::ZITE_URL_LOGIN,self::ZITE_URL_CREATE);
		if(in_array($endpoint,$endpoints) === false){
			if($this->accessToken !== null){
				$defaultParams['accessToken'] = $this->accessToken;
			}

			if($this->userId !== null){
				$defaultParams['userId'] = $this->userId;
			}
		}

		$params = $defaultParams +  $reqParams;
		$queryStr = http_build_query($params);

		$this->url = "{$this->serverUrl}/{$this->apiVersion}/{$endpoint}/?{$queryStr}";
	}

	/**
	 * Set the method used for the CURL request
	 * @param string $method Valid method GET, POST
	 * @return bool True on success
	 * @throws ZiteException
	 */
	protected function setMethod($method){
		if(in_array(strtoupper($method),$this->validMethods)){
			$this->method = strtoupper($method);
		}
		else{
			throw new ZiteException("{$method} is not a valid method.");
		}
		return true;
	}

	/**
	 * Performs the CURL request
	 * @return mixed Result from the CURL request
	 * @throws ZiteException
	 */
	protected function request() {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->method);
		$returnData = curl_exec($curl);
		$this->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($errorNo = curl_errno($curl)){
			$curlError = curl_error($curl);
			error_log($curlError,$errorNo);
			curl_close($curl);
			throw new ZiteException($curlError,$errorNo);
		}

		curl_close($curl);
		$this->method = 'GET';//Set the method back to get

		$jsonData = json_decode($returnData);
		if(empty($jsonData)){
			$jsonData = new \stdClass();
			$jsonData->error = "No data received from from server ({$this->httpCode})";
		}

		if($this->inHttpRange($this->httpCode,200)){
			$this->error = false;
			$this->errorMsg = null;
		}
		else{
			$this->error = true;
			$this->errorMsg = $jsonData->error;
			error_log($jsonData->error);
			if($this->throwExceptionOnError === true){
				throw new ZiteException($jsonData->error,$this->httpCode);
			}
		}

		return $returnData;

	}

	/**
	 * Check if status is in a certain http range
	 * @param $status The current HTTP status code
	 * @param int $range Lowest number in the range to check
	 * @return bool True if in range else false
	 */
	private function inHttpRange ($status,$range = 200){
		$max =  (ceil(($range + 1) / 100) * 100) -1;
		return ($status >= $range && $status <= $max);
	}
}