<?php
/**
 * @author Robin Appelman <icewind@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Conversations\Controller;

use OCA\Conversations\Conversation\Manager;
use OCA\Conversations\Conversation\Message;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class APIController extends Controller {
	/**
	 * @var \OCA\Conversations\Conversation\Manager
	 */
	private $manager;

	/**
	 * @var \OCP\IUserSession
	 */
	private $userSession;

	/**
	 * @var \OCP\IConfig
	 */
	private $config;

	/**
	 * APIController constructor.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param \OCA\Conversations\Conversation\Manager $manager
	 * @param \OCP\IUserSession $userSession
	 * @param \OCP\IConfig $config
	 */
	public function __construct($appName, IRequest $request, Manager $manager, IUserSession $userSession, IConfig $config) {
		parent::__construct($appName, $request);
		$this->manager = $manager;
		$this->userSession = $userSession;
		$this->config = $config;
	}


	/**
	 * @NoAdminRequired
	 *
	 * @param int $roomId
	 * @param int $page
	 * @param int $from
	 * @return JSONResponse
	 */
	public function getMessages($roomId, $page, $from) {
		$room = $this->manager->getRoomById($roomId);

		$count = 25;

		$messages = $room->getMessages($from, $count, $page * $count);

		$formattedMessages = array_map(function (Message $message) {
			return $message->format();
		}, $messages);

		return new JSONResponse($formattedMessages);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $roomId
	 * @param string $text
	 * @return JSONResponse
	 */
	public function newMessage($roomId, $text) {
		$room = $this->manager->getRoomById($roomId);
		$user = $this->userSession->getUser();

		$message = $room->newMessage($user->getUID(), $text);

		return new JSONResponse($message, Http::STATUS_CREATED);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function listRooms() {
		return new JSONResponse([]);
	}
}
