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

class Attachment {
	const TYPE_INTERNAL = 1;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $message;

	/**
	 * @var int
	 */
	private $type;

	/**
	 * @var string
	 */
	private $source;

	/**
	 * Attachment constructor.
	 *
	 * @param int $id
	 * @param int $message
	 * @param int $type
	 * @param string $source
	 */
	public function __construct($id, $message, $type, $source) {
		$this->id = $id;
		$this->message = $message;
		$this->type = $type;
		$this->source = $source;
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
	public function getMessage() {
		return $this->message;
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
	public function getSource() {
		return $this->source;
	}

	/**
	 * @return string
	 */
	public function getDownloadLink() {
		//TODO
	}

	public function format() {
		return [
			'type' => 'internal',
			'fileid' => $this->source
		];
	}
}
