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

namespace OCA\Conversations\AppInfo;

use OCA\Conversations\Conversation\Manager;
use OCA\Conversations\Conversation\Migrator;
use OCP\AppFramework\App;
use OCP\IContainer;

class Application extends App {
	public function __construct(array $urlParams = array()) {
		parent::__construct('conversations', $urlParams);
		$container = $this->getContainer();

		/**
		 * Conversations Services
		 */
		$container->registerService('Manager', function (IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			return new Manager(
				$server->getDatabaseConnection()
			);
		});

		$container->registerService('ConversationsL10N', function (IContainer $c) {
			return $c->query('ServerContainer')->getL10N('conversations');
		});


		$container->registerService('Migrator', function (IContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');

			return new Migrator(
				$server->getDatabaseConnection(),
				$server->getConfig(),
				$c->query('Manager')
			);
		});
	}

	/**
	 * @return \OCA\Conversations\Conversation\Manager
	 */
	public function getManager() {
		return $this->getContainer()->query('Manager');
	}

	/**
	 * @return \OCA\Conversations\Conversation\Migrator
	 */
	public function getMigrator() {
		return $this->getContainer()->query('Migrator');
	}
}

