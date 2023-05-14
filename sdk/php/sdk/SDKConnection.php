<?php
/******************************************************************************
 *
 *  Copyright 2023 Paphus Solutions Inc.
 *
 *  Licensed under the Eclipse Public License, Version 1.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.eclipse.org/legal/epl-v10.html
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 ******************************************************************************/


require_once "Credentials.php";
require_once('./config/UserConfig.php');
require_once('./config/Config.php');
require_once('./config/ChatConfig.php');
require_once('./config/ChatResponse.php');
require_once('./config/DomainConfig.php');
require_once('./config/ForumPostConfig.php');
require_once('./config/UserMessageConfig.php');
require_once('./config/ResponseConfig.php');

class SDKConnection
{
	protected static $types = array("Bots", "Forums", "Graphics", "Live Chat", "Domains", "Scripts", "IssueTracker");
	protected static $channelTypes = array("ChatRoom", "OneOnOne");
	protected static $accessModes = array("Everyone", "Users", "Members", "Administrators");
	protected static $mediaAccessModes = array("Everyone", "Users", "Members", "Administrators", "Disabled");
	protected static $learningModes = array("Disabled", "Administrators", "Users", "Everyone");
	protected static $correctionModes = array("Disabled", "Administrators", "Users", "Everyone");
	protected static $botModes = array("ListenOnly", "AnswerOnly", "AnswerAndListen");
	protected string $url;
	protected ?UserConfig $user;
	protected ?DomainConfig $domain;
	protected Credentials $credentials;
	protected bool $debug = false;

	protected SDKException $exception;


	/**
	 * Return the name of the default user image.
	 */
	function defaultUserImage()
	{
		return "images/user-thumb.jpg";
	}
	/**
	 * Create an SDK connection with the credentials.
	 * Use the Credentials subclass specific to your server.
	 */
	public function __construct(Credentials $credentials)
	{
		$this->credentials = $credentials;
		$this->url = $credentials->url;
	}
	/**
	 * Validate the user credentials (password, or token).
	 * The user details are returned (with a connection token, password removed).
	 * The user credentials are soted in the connection, and used on subsequent calls.
	 * An SDKException is thrown if the connect failed.
	 */
	public function connect(UserConfig $config): ?UserConfig
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/check-user", $config->toXML());
		if ($xml == null) {
			$this->user == null;
			return null;
		}
		try {
			$user = new UserConfig();
			$user->parseXML($xml);
			$this->user = $user;
		} catch (Exception $exception) {
			echo "Exception: " . $exception->getMessage() . "\n";
		}

		return $this->user;
	}

	/**
	 * Execute the custom API.
	 */
	public function custom(string $api, Config $config, Config $result): ?Config
	{ //Need Testing
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/" . $api, $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$result->parseXML($xml);
		} catch (Exception $exception) {

			echo "Error: " + $exception->getMessage();
		}
		return $result;
	}

	/**
	 * Connect to the live chat channel and return a LiveChatConnection.
	 * A LiveChatConnection is separate from an SDKConnection and uses web sockets for
	 * asynchronous communication.
	 * The listener will be notified of all messages.
	 */
	// public function openLiveChat(ChannelConfig $channel, LiveChatListener $listener) : LiveChatConnection {
	// 	LiveChatConnection $connection = new LiveChatConnection($this->credentials, $listener);
	// 	$connection->connect($channel, $this->user);
	// 	return $connection;
	// }

	/**
	 * Connect to the domain.
	 * A domain is an isolated content space.
	 * Any browse or query request will be specific to the domain's content.
	 */
	// public function connect(DomainConfig $config) : DomainConfig {
	// 	$this->domain = fetch(config);
	// 	return $this->domain;
	// }

	/**
	 * Disconnect from the connection.
	 * An SDKConnection does not keep a live connection, but this resets its connected user and domain.
	 */
	public function disconnect()
	{
		$this->user = null;
		$this->domain = null;
	}

	/**
	 * Process the bot chat message and return the bot's response.
	 * The ChatConfig should contain the conversation id if part of a conversation.
	 * If a new conversation the conversation id i returned in the response.
	 */
	public function chat(ChatConfig $config): ?ChatResponse
	{ //Tested
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/post-chat", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$response = new ChatResponse();
			$response->parseXML($xml);
			return $response;
		} catch (Exception $exception) {
			echo "Error: " + $exception;
		}
	}


	/**
	 * Process the avatar message and return the avatars response.
	 * This allows the speech and video animation for an avatar to be generated for the message.
	 */
	public function avatarMessage(AvatarMessage $config): ?ChatResponse
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/avatar-message", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$response = new ChatResponse();
			$response->parseXML($xml);
			return $response;
		} catch (Exception $exception) {
			echo "Error: " + $exception; //Misssing implementation of SDKException.parseFailure(exception);
		}
	}


	/**
	 * Fetch the user details.
	 * Function names can't be the same.
	 */
	public function fetchUser(UserConfig $config): ?UserConfig
	{ //Need Testing
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/view-user", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$user = new UserConfig();
			$user->parseXML($xml);
			return $user;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}

	/**
	 * Fetch the URL for the image from the server.
	 */
	// public function fetchImage(String $image) : URL {
	// 	try {
	// 		return new URL("http://" . $this->credentials->host + $this->credentials->app . "/" + $image);
	// 	} catch (Exception $exception) {
	// 		echo "Error: " . $exception->getMessage();
	// 	}
	// }

	/**
	 * Fetch the forum post details for the forum post id.
	 */
	public function fetchForumPost(ForumPostConfig $config): ?ForumPostConfig
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/check-forum-post", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$post = new ForumPostConfig();
			$post->parseXML($xml);
			return $post;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}

	/**
	 * Create a new user.
	 */
	public function createUser(UserConfig $config): ?UserConfig
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/create-user", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$user = new UserConfig();
			$user->parseXML($xml);
			$this->user = $user;
			return $user;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}

	/**
	 * Create a new forum post.
	 * You must set the forum id for the post.
	 */
	public function createForumPost(ForumPostConfig $config): ?ForumPostConfig
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/create-forum-post", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$post = new ForumPostConfig();
			$post->parseXML($xml);
			return $post;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}

	/**
	 * Create a new file/image/media attachment for a chat channel.
	 */
	// public MediaConfig createChannelFileAttachment(String file, MediaConfig config) {
	// 	config.addCredentials(this);
	// 	String xml = POSTFILE(this.url + "/create-channel-attachment", file, config.name, config.toXML());
	// 	Element root = parse(xml);
	// 	if (root == null) {
	// 		return null;
	// 	}
	// 	try {
	// 		MediaConfig media = new MediaConfig();
	// 		media.parseXML(root);
	// 		return media;
	// 	} catch (Exception exception) {
	// 		this.exception = SDKException.parseFailure(exception);
	// 		throw this.exception;
	// 	}
	// }


	/**
	 * Create a new file/image/media attachment for a chat channel.
	 */
	// public MediaConfig createChannelImageAttachment(String file, MediaConfig config) {
	// 	config.addCredentials(this);
	// 	String xml = POSTIMAGE(this.url + "/create-channel-attachment", file, config.name, config.toXML());
	// 	Element root = parse(xml);
	// 	if (root == null) {
	// 		return null;
	// 	}
	// 	try {
	// 		MediaConfig media = new MediaConfig();
	// 		media.parseXML(root);
	// 		return media;
	// 	} catch (Exception exception) {
	// 		this.exception = SDKException.parseFailure(exception);
	// 		throw this.exception;
	// 	}
	// }

	/**
	 * Create a reply to a forum post.
	 * You must set the parent id for the post replying to.
	 */
	public function createReply(ForumPostConfig $config): ?ForumPostConfig
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/create-reply", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$reply = new ForumPostConfig();
			$reply->parseXML($xml);
			return $reply;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}

	/**
	 * Create a user message.
	 * This can be used to send a user a direct message.
	 * SPAM will cause your account to be deleted.
	 */
	public function createUserMessage(UserMessageConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/create-user-message", $config->toXML());
	}

	/**
	 * Update the forum post.
	 */
	public function updateForumPost(ForumPostConfig $config): ?ForumPostConfig
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/update-forum-post", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$config = new ForumPostConfig();
			$config->parseXML($xml);
			return $config;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}

	/**
	 * Create or update the response.
	 * This can also be used to flag, unflag, validate, or invalidate a response.
	 */
	public function saveResponse(?ResponseConfig $config): ?ResponseConfig
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/save-response", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$response = new ResponseConfig();
			$response->parseXML($xml);
			return $response;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}


	/**
	 * Return the administrators of the content.
	 */
	public function getAdmins(WebMediumConfig $config)
	{
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/get-" . $config->getType() . "-admins", $config->toXML());
		$users = array();
		if ($xml == null) {
			return $users;
		}
		$xmlData = simplexml_load_string($xml);
		if ($xmlData === false) {
			echo "Failed loading XML: ";
			foreach (libxml_get_errors() as $error) {
				echo "<br>", $error->message;
			}
		}
		// else {
		//     print_r($xmlData);
		// }
		// for (int index = 0; index < root.getChildNodes().getLength(); index++) {
		// 	UserConfig user = new UserConfig();
		// 	user.parseXML((Element)root.getChildNodes().item(index));
		// 	users.add(user.user);
		// }
		// return users;

		return $xmlData;
	}



	/**
	 * Permanently delete the forum post with the id.
	 */
	public function deleteForumPost(ForumPostConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/delete-forum-post", $config->toXML());
	}

	/**
	 * Permanently delete the response, greetings, or default response with the response id (and question id).
	 */
	public function deleteResponse(ResponseConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/delete-response", $config->toXML());
	}

	/**
	 * Permanently delete the avatar media.
	 */
	public function deleteAvatarMedia(AvatarMedia $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/delete-avatar-media", $config->toXML());
	}

	/**
	 * Permanently delete the avatar background.
	 */
	public function deleteAvatarBackground(AvatarConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/delete-avatar-background", $config->toXML());
	}

	/**
	 * Save the avatar media tags.
	 */
	public function saveAvatarMedia(AvatarMedia $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/save-avatar-media", $config->toXML());
	}

	/**
	 * Subscribe for email updates for the post.
	 */
	public function subscribeForumPost(ForumPostConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/subscribe-post", $config->toXML());
	}

	/**
	 * Subscribe for email updates for the forum.
	 */
	public function subscribeForum(ForumConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/subscribe-forum", $config->toXML());
	}


	/**
	 * Unsubscribe from email updates for the post.
	 */
	public function unsubscribeForumPost(ForumPostConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/unsubscribe-post", $config->toXML());
	}

	/**
	 * Unsubscribe for email updates for the forum.
	 */
	public function unsubscribeForum(ForumConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/unsubscribe-forum", $config->toXML());
	}

	/**
	 * Thumbs up the content.
	 */
	public function thumbsUp(WebMediumConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/thumbs-up-" . $config->getType(), $config->toXML());
	}

	/**
	 * Thumbs down the content.
	 */
	public function thumbsDown(WebMediumConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/thumbs-down-" . $config->getType(), $config->toXML());
	}

	/**
	 * Rate the content.
	 */
	public function star(WebMediumConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/star-" . $config->getType(), $config->toXML());
	}

	/**
	 * Thumbs up the content.
	 */
	public function thumbsUpPost(ForumPostConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/thumbs-up-post", $config->toXML());
	}

	/**
	 * Thumbs down the content.
	 */
	public function thumbsDownPost(ForumPostConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/thumbs-down-post", $config->toXML());
	}

	/**
	 * Rate the content.
	 */
	public function starPost(ForumPostConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/start-post", $config->toXML());
	}

	/**
	 * Flag the forum post as offensive, a reason is required.
	 */
	public function flagForumPost(ForumPostConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/flag-forum-post", $config->toXML());
	}

	/**
	 * Flag the user as offensive, a reason is required.
	 */
	public function flagUser(UserConfig $config): void
	{
		$config->addCredentials($this);
		$this->POST($this->url . "/flag-user", $config->toXML());
	}

	/**
	 * Return the bot's learning configuration.
	 */
	public function getLearning(InstanceConfig $config) : ?LearningConfig {
		$config->addCredentials($this);
		$xml = $this->POST($this->url . "/get-learning", $config->toXML());
		if ($xml == null) {
			return null;
		}
		try {
			$learning = new LearningConfig();
			$learning->parseXML($xml);
			return $learning;
		} catch (Exception $exception) {
			echo "Error: " . $exception->getMessage();
		}
	}


	/**
	 * Process the speech message and return the server generate text-to-speech audio file.
	 * This allows for server-side speech generation.
	 */
	public function tts(Speech $config): ?string
	{
		$config->addCredentials($this);
		return $this->POST($this->url . "/speak", $config->toXML());
	}



	/**
	 * Return the list of content types.
	 */
	public function getTypes() {
		return $this->types;
	}
	
	/**
	 * Return the channel types.
	 */
	public function getChannelTypes() {
		return $this->channelTypes;
	}
	
	/**
	 * Return the access mode types.
	 */
	public function getAccessModes() {
		return $this->accessModes;
	}
	
	/**
	 * Return the media access mode types.
	 */
	public function getMediaAccessModes() {
		return $this->mediaAccessModes;
	}
	
	/**
	 * Return the learning mode types.
	 */
	public function getLearningModes() {
		return $this->learningModes;
	}
	
	/**
	 * Return the correction mode types.
	 */
	public function getCorrectionModes() {
		return $this->correctionModes;
	}
	
	/**
	 * Return the bot mode types.
	 */
	public function getBotModes() {
		return $this->botModes;
	}

	/**
	 * Return the current connected user.
	 */
	public function getUser(): UserConfig
	{
		return $this->user;
	}

	/**
	 * Set the current connected user.
	 * connect() should be used to validate and connect a user.
	 */
	public function setUser(UserConfig $user): void
	{
		$this->user = $user;
	}

	/**
	 * Return the current domain.
	 * A domain is an isolated content space.
	 */
	public function getDomain(): ?DomainConfig
	{
		if (isset($this->domain)) {
			return $this->domain;
		}
		return null;
	}

	/**
	 * Set the current domain.
	 * A domain is an isolated content space.
	 * connect() should be used to validate and connect a domain.
	 */
	public function setDomain(DomainConfig $domain): void
	{
		$this->domain = $domain;
	}

	/**
	 * Return the current application credentials.
	 */
	public function getCredentials(): ?Credentials
	{
		if ($this->credentials == null) {
			var_dump($this->credentials);
			echo "(SDKConnection) This credentials is null.";
			return null;
		}
		return $this->credentials;
	}

	/**
	 * Set the application credentials.
	 */
	public function setCredentials(Credentials $credentials): void
	{
		$this->credentials = $credentials;
		$this->url = $credentials->url;
	}

	/**
	 * Return is debugging has been enabled.
	 */
	public function isDebug(): bool
	{
		return $this->debug;
	}

	/**
	 * Enable debugging, debug messages will be logged to System.out.
	 */
	public function setDebug(bool $debug): void
	{
		$this->debug = $debug;
	}
	// public Element parse(String xml) {
	// 	if (this.debug) {
	// 		System.out.println(xml);
	// 	}
	// 	Document dom = null;
	// 	DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
	// 	try {
	// 		DocumentBuilder builder = factory.newDocumentBuilder();
	// 		InputSource source = new InputSource();
	// 		source.setCharacterStream(new StringReader(xml));
	// 		dom = builder.parse(source);
	// 		return dom.getDocumentElement();
	// 	} catch (Exception exception) {
	// 		if (this.debug) {
	// 			exception.printStackTrace();
	// 		}
	// 		this.exception = new SDKException(exception.getMessage(), exception);
	// 		throw this.exception;
	// 	}
	// }



	public function GET(string $url) : string {
		if ($this->debug) {
			$debugComment = "GET_URL: " . $url;
			$debugInfo = $url;
			include "./views/debug.php";
		}
		$ch = curl_init();
		$xmlData = simplexml_load_string($xml) or die("Error: Prior of xml request. Cannot create object");
		if ($this->debug) {
			$debugComment = "GET: Sending xml request.";
			$debugInfo = $xmlData;
			include "./views/debug.php";
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_HEADER, 1);

		// $headers = [
		// 	'Content-Type: application/xml',
		// 	'Accept: application/xml'
		// ];

		// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		if ($e = curl_error($ch)) {
			echo $e;
		} else {
			if ($this->debug) {
				if (isset($response) || $response !== null) {
					$result = simplexml_load_string($response) or die("Error: Cannot create object");
					$debugComment = "Result after the request.";
					$debugInfo = $result;
					include "./views/debug.php";
				}
			}
		}
		curl_close($ch);
		return $response;
	}
	public function POST(string $url, string $xml): string
	{
		if ($this->debug) {
			$debugComment = "POST_URL: " . $url;
			$debugInfo = htmlentities($xml);
			include "./views/debug.php";
		}
		$ch = curl_init();
		$xmlData = simplexml_load_string($xml) or die("Error: Prior of xml request. Cannot create object");
		if ($this->debug) {
			$debugComment = "POST: Sending xml request.";
			$debugInfo = $xmlData;
			include "./views/debug.php";
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); //It needs the actual xml text not the object
		// The result of simplexml_load_string($xml) passing a string xml will return a data object xml.
		// curl_setopt just need a string text of the xml.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_HEADER, 1);

		$headers = [
			'Content-Type: application/xml',
			'Accept: application/xml'
		];

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		if ($e = curl_error($ch)) {
			echo $e;
		} else {
			if ($this->debug) {
				if (isset($response) || $response !== null) {
					$result = simplexml_load_string($response) or die("Error: Cannot create object");
					$debugComment = "Result after the request.";
					$debugInfo = $result;
					include "./views/debug.php";
				}
			}
		}
		curl_close($ch);
		return $response;
	}
}
?>