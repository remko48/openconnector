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
 * - Removing of SynchronizationContract columns sourceId & sourceHash
 */
class Version1Date20241112143200 extends SimpleMigrationStep {

	private IDBConnection $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

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

		// Step 3: Drop the old columns after data migration
		/**
		 * @var ISchemaWrapper $schema
		 */
		$schema = $schemaClosure();
		$table = $schema->getTable('openconnector_synchronization_contracts');

		if ($table->hasColumn('source_id') === true) {
			$table->dropColumn('source_id');
		}
		if ($table->hasColumn('source_hash') === true) {
			$table->dropColumn('source_hash');
		}

		return $schema;
	}
}
