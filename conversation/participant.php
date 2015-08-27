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

class Participant {
	const TYPE_USER = 1;

	const TYPE_GROUP = 2;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $roomId;

	/**
	 * @var int self::TYPE_USER or self::TYPE_GROUP
	 */
	private $type;

	/**
	 * @var string
	 */
	private $user;

	/**
	 * Participant constructor.
	 *
	 * @param int $id
	 * @param int $roomId
	 * @param int $type
	 * @param string $user
	 */
	public function __construct($id, $roomId, $type, $user) {
		$this->id = $id;
		$this->roomId = $roomId;
		$this->type = $type;
		$this->user = $user;
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
	public function getRoomId() {
		return $this->roomId;
	}

	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}
}
