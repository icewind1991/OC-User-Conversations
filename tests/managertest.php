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

namespace OCA\Conversations\Tests;

use OCA\Conversations\Conversation\Manager;
use Test\TestCase;

class ManagerTest extends TestCase {
	private function deleteRoom($id) {
		$connection = \OC::$server->getDatabaseConnection();
		$query = $connection->prepare('DELETE FROM `*PREFIX*conv_rooms` WHERE `id` = ?');
		$query->execute([$id]);
		$query->closeCursor();

		$query = $connection->prepare('DELETE FROM `*PREFIX*conv_participants` WHERE `room_id` = ?');
		$query->execute([$id]);
		$query->closeCursor();

		$query = $connection->prepare('DELETE FROM `*PREFIX*conv_messages` WHERE `room_id` = ?');
		$query->execute([$id]);
	}

	public function testNewRoom() {
		$manager = new Manager(\OC::$server->getDatabaseConnection());
		$room = $manager->getRoomForUsers(['foo', 'bar']);

		$this->assertGreaterThan(0, $room->getId());
		$this->assertEquals(-1, $room->getLastMessageId());
		$this->assertCount(0, $room->getMessages());

		$this->deleteRoom($room->getId());
	}

	public function testExistingRoom() {
		$manager = new Manager(\OC::$server->getDatabaseConnection());
		$room = $manager->getRoomForUsers(['foo', 'bar']);

		$existing = $manager->getRoomForUsers(['bar', 'foo']);
		$this->assertEquals($room->getId(), $existing->getId());

		$this->deleteRoom($room->getId());
	}

	public function testRoomLastMessage() {
		$manager = new Manager(\OC::$server->getDatabaseConnection());
		$room = $manager->getRoomForUsers(['foo', 'bar']);
		$message = $room->newMessage('foo', 'bar');

		$existing = $manager->getRoomForUsers(['bar', 'foo']);
		$this->assertEquals($room->getId(), $existing->getId());
		$this->assertEquals($message->getId(), $existing->getLastMessageId());

		$this->deleteRoom($room->getId());
	}
}
