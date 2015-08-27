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

class Message {
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $room;

	/**
	 * @var string
	 */
	private $author;

	/**
	 * @var \DateTime
	 */
	private $date;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @var \OCA\Conversations\Conversation\Attachment[]
	 */
	private $attachments;


	/**
	 * Message constructor.
	 *
	 * @param \OCP\IDBConnection $connection
	 * @param int $id
	 * @param string $room
	 * @param string $author
	 * @param string $date
	 * @param string $text
	 * @param \OCA\Conversations\Conversation\Attachment[] $attachments
	 */
	public function __construct($connection, $id, $room, $author, $date, $text, $attachments = []) {
		$this->connection = $connection;
		$this->id = $id;
		$this->room = $room;
		$this->author = $author;
		$this->date = new \DateTime($date);
		$this->text = $text;
		$this->attachments = $attachments;
	}

	/**
	 * @return \OCP\IDBConnection
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getRoom() {
		return $this->room;
	}

	/**
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @return \OCA\Conversations\Conversation\Attachment[]
	 */
	public function getAttachments() {
		return $this->attachments;
	}

	/**
	 * @param int $type
	 * @param string $source
	 * @return \OCA\Conversations\Conversation\Attachment
	 */
	public function addAttachment($type, $source) {
		$query = $this->connection->prepare('INSERT INTO `*PREFIX*conv_attachments`(`message_id`, `type`, `source`) VALUES(?, ?, ?)');
		$query->execute([$this->id, $type, $source]);
		$id = $this->connection->lastInsertId('*PREFIX*conv_attachments');
		$attachment = new Attachment($id, $this->id, $type, $source);
		$this->attachments[] = $attachment;
		return $attachment;
	}

	public function format() {
		return [
			'id' => $this->id,
			'author' => $this->author,
			'text' => $this->text,
			'attachment' => (count($this->attachments) > 0)
				? $this->attachments[0]->format()
				: ''
		];
	}
}
