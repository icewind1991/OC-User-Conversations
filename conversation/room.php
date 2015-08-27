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

class Room {
	/**
	 * @var \OCP\IDBConnection
	 */
	private $connection;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $lastMessageId;

	/**
	 * @param \OCP\IDBConnection $connection
	 * @param int $id
	 * @param int $lastMessageId
	 */
	public function __construct($connection, $id, $lastMessageId) {
		$this->connection = $connection;
		$this->id = $id;
		$this->lastMessageId = $lastMessageId;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getLastMessageId() {
		return $this->lastMessageId;
	}

	/**
	 * @param int $from only return messages with an id higher than this
	 * @param int $count
	 * @param int $offset
	 * @return Message[]
	 */
	public function getMessages($from = 0, $count = 25, $offset = 0) {
		$query = $this->connection->prepare('SELECT `id`, `room_id`, `author`, `date`, `text` FROM *PREFIX*conv_messages WHERE
			`room_id` = ? AND `id` > ?', $count, $offset);
		$query->execute([$this->id, $from]);
		$rows = $query->fetchAll(\PDO::FETCH_ASSOC);
		$query->closeCursor();

		$messageIds = array_map(function ($row) {
			return $row['id'];
		}, $rows);

		$attachments = $this->loadAttachments($messageIds);

		return array_map(function ($row) use ($attachments) {
			return new Message(
				$this->connection,
				$row['id'],
				$row['room_id'],
				$row['author'],
				$row['date'],
				$row['text'],
				$attachments[$row['id']]
			);
		}, $rows);
	}

	/**
	 * @param int[] $messageIds
	 * @return \OCA\Conversations\Conversation\Attachment[][] [$messageId => Attachment[]]
	 */
	private function loadAttachments($messageIds) {
		if (count($messageIds) === 0) {
			return [];
		}
		$placeHolders = implode(',', array_fill(0, count($messageIds), '?'));
		$query = $this->connection->prepare('SELECT `id`, `message_id`, `type`, `source` from `*PREFIX*conv_attachments`
			WHERE `message_id` IN (' . $placeHolders . ')');
		$query->execute($messageIds);

		$attachments = $query->fetchAll(\PDO::FETCH_ASSOC);
		$query->closeCursor();

		$attachmentMap = [];
		foreach ($messageIds as $messageId) {
			$attachmentMap[$messageId] = [];
		}
		foreach ($attachments as $attachment) {
			$attachmentMap[$attachment['message_id']][] = new Attachment(
				$attachment['id'],
				$attachment['message_id'],
				$attachment['type'],
				$attachment['source']
			);
		}
		return $attachmentMap;
	}

	/**
	 * @param string $author
	 * @param string $text
	 * @param string|null $date in Y-m-d H:i:s format
	 * @return Message
	 */
	public function newMessage($author, $text, $date = null) {
		$query = $this->connection->prepare('INSERT INTO `*PREFIX*conv_messages` (room_id, author, date, text)
		 VALUES (?,?,?,?)');

		if (is_null($date)) {
			$date = date('Y-m-d H:i:s');
		}

		$query->execute(array(
			$this->id,
			$author,
			$date,
			$text,
		));

		$id = $this->connection->lastInsertId('*PREFIX*conv_messages');

		$query->closeCursor();

		$this->connection->executeUpdate('UPDATE `*PREFIX*conv_rooms` SET `last_message_id` = ? WHERE `id`', [$id, $this->getId()]);

		$this->lastMessageId = $id;

		return new Message($this->connection, $id, $this->id, $author, $date, $text);
	}
}
