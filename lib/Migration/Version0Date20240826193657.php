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
 */class Version0Date20240826193657 extends SimpleMigrationStep {

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

		if (!$schema->hasTable('openconnector_jobs')) {
			$table = $schema->createTable('openconnector_jobs');
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 20]);
			$table->addColumn('name', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('description', Types::TEXT, ['notnull' => false]);
			$table->addColumn('reference', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('version', Types::STRING, ['notnull' => false, 'length' => 50]);
			$table->addColumn('crontab', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('user_id', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('throws', Types::TEXT, ['notnull' => false]);
			$table->addColumn('data', Types::TEXT, ['notnull' => false]);
			$table->addColumn('last_run', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('next_run', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('is_enabled', Types::BOOLEAN, ['notnull' => true, 'default' => true]);
			$table->addColumn('date_created', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('date_modified', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('listens', Types::TEXT, ['notnull' => false]);
			$table->addColumn('conditions', Types::TEXT, ['notnull' => false]);
			$table->addColumn('class', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('priority', Types::INTEGER, ['notnull' => false]);
			$table->addColumn('async', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
			$table->addColumn('configuration', Types::TEXT, ['notnull' => false]);
			$table->addColumn('is_lockable', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
			$table->addColumn('locked', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
			$table->addColumn('last_run_time', Types::INTEGER, ['notnull' => false]);
			$table->addColumn('status', Types::BOOLEAN, ['notnull' => true, 'default' => true]);
			$table->addColumn('action_handler_configuration', Types::TEXT, ['notnull' => false]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('openconnector_logs')) {
			$table = $schema->createTable('openconnector_logs');
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 20]);
			$table->addColumn('type', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('call_id', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('request_method', Types::STRING, ['notnull' => false, 'length' => 10]);
			$table->addColumn('request_headers', Types::TEXT, ['notnull' => false]);
			$table->addColumn('request_query', Types::TEXT, ['notnull' => false]);
			$table->addColumn('request_path_info', Types::TEXT, ['notnull' => false]);
			$table->addColumn('request_languages', Types::TEXT, ['notnull' => false]);
			$table->addColumn('request_server', Types::TEXT, ['notnull' => false]);
			$table->addColumn('request_content', Types::TEXT, ['notnull' => false]);
			$table->addColumn('response_status', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('response_status_code', Types::INTEGER, ['notnull' => false]);
			$table->addColumn('response_headers', Types::TEXT, ['notnull' => false]);
			$table->addColumn('response_content', Types::TEXT, ['notnull' => false]);
			$table->addColumn('user_id', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('session', Types::TEXT, ['notnull' => false]);
			$table->addColumn('session_values', Types::TEXT, ['notnull' => false]);
			$table->addColumn('response_time', Types::INTEGER, ['notnull' => false]);
			$table->addColumn('route_name', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('route_parameters', Types::TEXT, ['notnull' => false]);
			$table->addColumn('entity', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('endpoint', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('gateway', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('handler', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('object_id', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('date_created', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('date_modified', Types::DATETIME, ['notnull' => true]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('openconnector_mappings')) {
			$table = $schema->createTable('openconnector_mappings');
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 20]);
			$table->addColumn('reference', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('version', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('name', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('description', Types::TEXT, ['notnull' => false]);
			$table->addColumn('mapping', Types::TEXT, ['notnull' => false]);
			$table->addColumn('unset', Types::TEXT, ['notnull' => false]);
			$table->addColumn('cast', Types::TEXT, ['notnull' => false]);
			$table->addColumn('pass_trough', Types::BOOLEAN, ['notnull' => false]);
			$table->addColumn('date_created', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('date_modified', Types::DATETIME, ['notnull' => true]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('openconnector_sources')) {
			$table = $schema->createTable('openconnector_sources');
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 20]);
			$table->addColumn('name', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('description', Types::TEXT, ['notnull' => false]);
			$table->addColumn('reference', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('version', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('location', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('is_enabled', Types::BOOLEAN, ['notnull' => true, 'default' => true]);
			$table->addColumn('type', Types::STRING, ['notnull' => true, 'length' => 50]);
			$table->addColumn('authorization_header', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('auth', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('authentication_config', Types::TEXT, ['notnull' => false]);
			$table->addColumn('authorization_passthrough_method', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('locale', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('accept', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('jwt', Types::TEXT, ['notnull' => false]);
			$table->addColumn('jwt_id', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('secret', Types::TEXT, ['notnull' => false]);
			$table->addColumn('username', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('password', Types::TEXT, ['notnull' => false]);
			$table->addColumn('apikey', Types::TEXT, ['notnull' => false]);
			$table->addColumn('documentation', Types::TEXT, ['notnull' => false]);
			$table->addColumn('logging_config', Types::TEXT, ['notnull' => false]);
			$table->addColumn('oas', Types::TEXT, ['notnull' => false]);
			$table->addColumn('paths', Types::TEXT, ['notnull' => false]);
			$table->addColumn('headers', Types::TEXT, ['notnull' => false]);
			$table->addColumn('translation_config', Types::TEXT, ['notnull' => false]);
			$table->addColumn('configuration', Types::TEXT, ['notnull' => false]);
			$table->addColumn('endpoints_config', Types::TEXT, ['notnull' => false]);
			$table->addColumn('status', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('last_call', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('last_sync', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('object_count', Types::INTEGER, ['notnull' => false]);
			$table->addColumn('date_created', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('date_modified', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('test', Types::BOOLEAN, ['notnull' => false]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('openconnector_synchronizations')) {
			$table = $schema->createTable('openconnector_synchronizations');
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 20]);
			$table->addColumn('entity', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('object', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('action', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('gateway', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('sourceObject', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('endpoint', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('sourceId', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('hash', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('sha', Types::STRING, ['notnull' => false, 'length' => 255]);
			$table->addColumn('blocked', Types::BOOLEAN, ['notnull' => false]);
			$table->addColumn('sourceLastChanged', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('lastChecked', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('lastSynced', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('dateCreated', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('dateModified', Types::DATETIME, ['notnull' => true]);
			$table->addColumn('tryCounter', Types::INTEGER, ['notnull' => false]);
			$table->addColumn('dontSyncBefore', Types::DATETIME, ['notnull' => false]);
			$table->addColumn('mapping', Types::TEXT, ['notnull' => false]);
			$table->setPrimaryKey(['id']);
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
