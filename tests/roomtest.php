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

use OCA\Conversations\Conversation\Attachment;
use OCA\Conversations\Conversation\Room;
use Test\TestCase;

class RoomTest extends TestCase {
	public function tearDown() {
		$connection = \OC::$server->getDatabaseConnection();
		$query = $connection->prepare('DELETE FROM `*PREFIX*conv_messages` WHERE `room_id` < 0');
		$query->execute();
		$query->closeCursor();
		$query = $connection->prepare('DELETE FROM `*PREFIX*conv_attachments` WHERE `source` < 0');
		$query->execute();
	}

	public function testNewMessage() {
		$room = new Room(\OC::$server->getDatabaseConnection(), -1, -1);

		$message = $room->newMessage('foo', 'asd');
		$this->assertEquals('foo', $message->getAuthor());
		$this->assertEquals('asd', $message->getText());
		$this->assertEquals($message->getId(), $room->getLastMessageId());
	}

	public function testGetMessages() {
		$room = new Room(\OC::$server->getDatabaseConnection(), -1, -1);

		$room->newMessage('foo', 'asd');
		$room->newMessage('asd', 'bar');

		$messages = $room->getMessages();

		$this->assertCount(2, $messages);
		$this->assertEquals('foo', $messages[0]->getAuthor());
		$this->assertEquals('asd', $messages[1]->getAuthor());
	}

	public function testGetMessagesLimit() {
		$room = new Room(\OC::$server->getDatabaseConnection(), -1, -1);

		$room->newMessage('foo', 'asd');
		$room->newMessage('asd', 'bar');

		$messages = $room->getMessages(0, 1);

		$this->assertCount(1, $messages);
		$this->assertEquals('foo', $messages[0]->getAuthor());
	}

	public function testGetMessagesOffset() {
		$room = new Room(\OC::$server->getDatabaseConnection(), -1, -1);

		$room->newMessage('foo', 'asd');
		$room->newMessage('asd', 'bar');

		$messages = $room->getMessages(0, 25, 1);

		$this->assertCount(1, $messages);
		$this->assertEquals('asd', $messages[0]->getAuthor());
	}

	public function testGetMessagesAttachments() {
		$room = new Room(\OC::$server->getDatabaseConnection(), -1, -1);

		$message = $room->newMessage('foo', 'asd');
		$message->addAttachment(Attachment::TYPE_INTERNAL, -2);
		$message->addAttachment(Attachment::TYPE_INTERNAL, -3);
		$room->newMessage('asd', 'bar');
		$message = $room->newMessage('qwerty', 'bar');
		$message->addAttachment(Attachment::TYPE_INTERNAL, -4);

		$messages = $room->getMessages();

		$this->assertCount(3, $messages);
		$this->assertCount(2, $messages[0]->getAttachments());
		$this->assertCount(0, $messages[1]->getAttachments());
		$this->assertCount(1, $messages[2]->getAttachments());

		$this->assertEquals(-2, $messages[0]->getAttachments()[0]->getSource());
		$this->assertEquals(-3, $messages[0]->getAttachments()[1]->getSource());
		$this->assertEquals(-4, $messages[2]->getAttachments()[0]->getSource());
	}
}
