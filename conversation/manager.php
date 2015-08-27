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

use OCP\IDBConnection;

class Manager {
	/**
	 * @var \OCP\IDBConnection
	 */
	private $connection;

	/**
	 * Manager constructor.
	 *
	 * @param \OCP\IDBConnection $connection
	 */
	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param string $group
	 * @return \OCA\Conversations\Conversation\Room
	 */
	public function getRoomForGroup($group) {
		return $this->getRoomForParticipants([$group], Participant::TYPE_GROUP);
	}

	/**
	 * @param string[] $users
	 * @return \OCA\Conversations\Conversation\Room
	 */
	public function getRoomForUsers($users) {
		return $this->getRoomForParticipants($users, Participant::TYPE_USER);
	}

	/**
	 * @param string[] $participants
	 * @param int $type
	 * @return \OCA\Conversations\Conversation\Room
	 */
	private function getRoomForParticipants($participants, $type) {
		sort($participants);
		$placeHolders = implode(',', array_fill(0, count($participants), '?'));
		$query = $this->connection->prepare('SELECT `room_id` FROM `*PREFIX*conv_participants`
			WHERE `user` IN (' . $placeHolders . ') AND `type` = ?
			GROUP BY `room_id`
			HAVING COUNT(`id`) = ?');
		$query->execute(array_merge($participants, [$type, count($participants)]));
		$roomId = $query->fetchColumn();
		$query->closeCursor();

		if (!$roomId) {
			return $this->createRoom($participants, $type);
		} else {
			return $this->getRoomById($roomId);
		}
	}

	/**
	 * @param $participants
	 * @param $type
	 * @return \OCA\Conversations\Conversation\Room
	 */
	private function createRoom($participants, $type) {
		$query = $this->connection->prepare('INSERT INTO `*PREFIX*conv_rooms`(`last_message_id`) VALUES(?)');
		$query->execute([-1]);
		$query->closeCursor();
		$id = $this->connection->lastInsertId('*PREFIX*conv_rooms');

		$query = $this->connection->prepare('INSERT INTO `*PREFIX*conv_participants`(`room_id`, `type`, `user`) VAlUES(?, ?, ?)');
		foreach ($participants as $participant) {
			$query->execute([$id, $type, $participant]);
		}
		return new Room($this->connection, $id, -1);
	}

	public function getRoomById($roomId) {
		$query = $this->connection->prepare('SELECT `last_message_id` FROM `*PREFIX*conv_rooms` WHERE `id` = ?');
		$query->execute([$roomId]);
		$lastMessage = $query->fetchColumn();
		$query->closeCursor();

		return new Room($this->connection, $roomId, $lastMessage);
	}
}
