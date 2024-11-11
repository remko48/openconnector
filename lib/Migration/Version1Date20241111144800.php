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


/**
 * FIXME Auto-generated migration step: Please modify to your needs!
 *
 */class Version1Date20241111144800 extends SimpleMigrationStep {

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

		// Update the openconnector_synchronization_contracts table
		$table = $schema->getTable('openconnector_synchronization_contracts');
		// Check if the column exists
		if ($table->hasColumn('source_id') === true) {
			// Rename the column 'old_column_name' to 'new_column_name'
			$table->renameColumn('source_id', 'origin_id');
		}
		// Check if the column exists
		if ($table->hasColumn('source_hash') === true) {
			// Rename the column 'old_column_name' to 'new_column_name'
			$table->renameColumn('source_hash', 'origin_hash');
		}

		// Check if the index exists
		if ($table->hasIndex('openconnector_sync_contracts_source_id_index') === true) {
			// Remove the old index
			$table->dropIndex('openconnector_sync_contracts_origin_id_index');
		}
		// Check if the index exists
		if ($table->hasIndex('openconnector_sync_contracts_origin_id_index') === false) {
			// Add a new index with the desired name
			$table->addIndex(['origin_id'], 'openconnector_sync_contracts_origin_id_index');
		}

		// Check if the index exists
		if ($table->hasIndex('openconnector_sync_contracts_sync_source_index') === true) {
			// Remove the old index
			$table->dropIndex('openconnector_sync_contracts_sync_source_index');
		}
		// Check if the index exists
		if ($table->hasIndex('openconnector_sync_contracts_sync_origin_index') === false) {
			// Add a new index with the desired name
			$table->addIndex(['synchronization_id', 'origin_id'], 'openconnector_sync_contracts_sync_origin_index');
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
