<?php

/**
 * ownCloud - User Conversations
 *
 * @author Simeon Ackermann
 * @copyright 2014 Simeon Ackermann amseon@web.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Conversations\Conversation;

use OCP\IConfig;
use OCP\IDBConnection;

class Migrator {
	/**
	 * @var \OCP\IDBConnection
	 */
	private $connection;

	/**
	 * @var \OCP\IConfig
	 */
	private $config;

	/**
	 * @var \OCA\Conversations\Conversation\Manager
	 */
	private $manager;

	/**
	 * @var \OCA\Conversations\Conversation\Room[]
	 */
	private $roomCache = [];

	/**
	 * @param \OCP\IDBConnection $connection
	 * @param \OCP\IConfig $config
	 * @param Manager $manager
	 */
	public function __construct(IDBConnection $connection, IConfig $config, Manager $manager) {
		$this->connection = $connection;
		$this->config = $config;
		$this->manager = $manager;
	}

	public function needsMigrate() {
		$installedVersion = $this->config->getAppValue('conversations', 'installed_version');
		return version_compare($installedVersion, '0.3.0', '<');
	}

	private function getOldMessages() {
		$query = $this->connection->prepare('SELECT `id`, `author`, `date`, `text`, `attachment`, `room` FROM `*PREFIX*conversations`');
		$query->execute();
		return $query->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function migrate() {
		$oldMessages = $this->getOldMessages();

		foreach ($oldMessages as $oldMessage) {
			$room = $this->getRoom($oldMessage['author'], $oldMessage['room']);
			$message = $room->newMessage($oldMessage['author'], $oldMessage['text'], $oldMessage['date']);
			if ($oldMessage['attachment']) {
				list($type, $source) = $this->parseOldAttachment($oldMessage['attachment']);
				$message->addAttachment($type, $source);
			}
		}
	}

	private function parseOldAttachment($data) {
		$json = json_decode($data, true);
		return [Attachment::TYPE_INTERNAL, $json['fileid']];
	}

	private function getRoom($author, $oldRoom) {
		$key = $author . ',' . $oldRoom;
		if (isset($this->roomCache[$key])) {
			return $this->roomCache[$key];
		}
		list($typeString, $participant) = explode(':', $oldRoom);
		if ($typeString === 'user') {
			$room = $this->manager->getRoomForUsers([$author, $participant]);
		} else {
			$room = $this->manager->getRoomForGroup($participant);
		}

		$this->roomCache[$key] = $room;
		return $room;
	}
}
