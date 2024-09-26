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

class Version0Date20240926235025 extends SimpleMigrationStep {

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
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('openconnector_call_logs')) {
            $table = $schema->createTable('openconnector_call_logs');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('status_code', 'integer', [
                'notnull' => false,
                'length' => 3
            ]);
            $table->addColumn('status_message', 'string', [
                'notnull' => false,
                'length' => 256
            ]);
            $table->addColumn('request', 'json', [
                'notnull' => false,
            ]);
            $table->addColumn('response', 'json', [
                'notnull' => false,
            ]);
            $table->addColumn('source_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('action_id', 'integer', [
                'notnull' => false,
            ]);
            $table->addColumn('synchronization_id', 'integer', [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', 'datetime',  [
                'notnull' => true, 
                'default' => 'CURRENT_TIMESTAMP'
            ]);
            $table->addColumn('updated_at', 'datetime',  [
                'notnull' => true, 
                'default' => 'CURRENT_TIMESTAMP'
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['source_id'], 'openconnector_call_logs_source_id_index');
            $table->addIndex(['action_id'], 'openconnector_call_logs_action_id_index');
            $table->addIndex(['synchronization_id'], 'openconnector_call_logs_sync_id_index');
            $table->addIndex(['status_code'], 'openconnector_call_logs_status_code_index');
        }

        return $schema;
    }
}