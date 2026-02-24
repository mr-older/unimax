<?php

namespace UniMax;

class Message
{
	public $error, $url = "https://platform-api.max.ru/";

	function __construct($user_id, $access_token)
	{
		if(!empty($access_token)) {
			$this->error = "No access_token";
			return false;
		}

		$this->access_token = $access_token;

		if(!empty($user_id)) {
			$this->error = "No user id";
			return false;
		}

		$this->user_id = $user_id;

		if(!($this->curl_handle = curl_init())) {
			$this->error = "Failed to create CURL handle @$api_auth_url";
			return false;
		}

		return true;
	}

 	private function setQueryParams($params = [])
	{
		$url = $this->url."messages";
		if(empty($params)) return $url;
		$url.= "?";

		foreach($params as $key => $value) {
			$url.="$key=$value&";
		}

		return trim($url, "&");
	}

 	private function makePayload($request, $text)
	{
		switch($request) {
			case "message":
				$payload = [
					"text" => $text
				];
			 break;
			default: break;
		}
/*
  "attachments": [{
      "type": "inline_keyboard",
      "payload": {
        "buttons": [
          [
            {
              "type": "link",
              "text": "Link here",
              "url": "https://example.com"
            }
          ]
        ]
      }
    }]
 */
		return $payload;
	}

 	public function getGroupChats()
	{
		$url = "https://platform-api.max.ru/chats";
		if(($answer = $this->do_CURL_GET($url)) === false) {
			$this->error = "CURL error @{$this->api_url}";
			return false;
		}

		return $answer;
	}

 	public function sendMessage($chat_id, $text, $keyboard = null)
	{
		$query_params = [];
		$query_params["chat_id"] = $chat_id; //-71219747198340;
		$query_params["user_id"] = $this->user_id;
//
#		$query_params["disable_link_preview"] = false;
		$url = $this->setQueryParams($query_params);
		$payload = $this->makePayload("message", $text);
#		var_dump($url);
		if(($answer = $this->do_CURL_POST($url, json_encode($payload))) === false) {
			$this->error = "Request error @{$this->api_url}";
			return false;
		}

		if(empty($answer) || ($dejsoned_answer = json_decode($answer, true)) === false) {
			$this->error = "Can`t dejson answer from $url";
			return false;
		}

		return $dejsoned_answer;
	}

	private function do_CURL_POST($api_url, $jsonString)
	{
		curl_setopt($this->curl_handle, CURLOPT_POST, 1);
		curl_setopt($this->curl_handle, CURLOPT_URL, $api_url);
		curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, array("Authorization: {$this->access_token}", "Content-Type: application/json"));
		curl_setopt($this->curl_handle, CURLOPT_HEADER, false);
		curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, $jsonString);
#		curl_setopt($this->curl_handle, CURLOPT_COOKIE, "sid={$this->access_token}");
		$answer = curl_exec($this->curl_handle);
		curl_close($this->curl_handle);
		return $answer;
	}

	private function do_CURL_GET($api_url)
	{
		curl_setopt($this->curl_handle, CURLOPT_POST, 0);
		curl_setopt($this->curl_handle, CURLOPT_URL, $api_url);
		curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, array("Authorization: {$this->access_token}"));
		curl_setopt($this->curl_handle, CURLOPT_HEADER, false);
		curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		$answer = curl_exec($this->curl_handle);
		curl_close($this->curl_handle);
		return $answer;
	}
}
