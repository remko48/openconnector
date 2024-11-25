<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\OpenConnector\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\IDBConnection;

/**
 * This migration changes the following:
 * - Adding 3 new columns for the table Source: rateLimitLimit, rateLimitRemaining & rateLimitReset
 */
class Version1Date20241121160300 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/**
		 * @var ISchemaWrapper $schema
		 */
		$schema = $schemaClosure();
		$table = $schema->getTable('openconnector_source');

		if ($table->hasColumn('rateLimitLimit') === false) {
			$table->addColumn('rateLimitLimit', Types::INTEGER, [
				'notnull' => false,
				'default' => null
			]);
		}

		if ($table->hasColumn('rateLimitRemaining') === false) {
			$table->addColumn('rateLimitRemaining', Types::INTEGER, [
				'notnull' => false,
				'default' => null
			]);
		}

		if ($table->hasColumn('rateLimitReset') === false) {
			$table->addColumn('rateLimitReset', Types::INTEGER, [
				'notnull' => false,
				'default' => null
			]);
		}

		if ($table->hasColumn('rateLimitWindow') === false) {
			$table->addColumn('rateLimitWindow', Types::INTEGER, [
				'notnull' => false,
				'default' => null
			]);
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
