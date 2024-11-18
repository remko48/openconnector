<?php

declare(strict_types=1);

namespace OCA\OpenConnector\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1Date20240319144900 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('openconnector_applications')) {
            $table = $schema->createTable('openconnector_applications');
            
            // Primary key
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
                'length' => 20,
            ]);

            // Basic information
            $table->addColumn('uuid', Types::STRING, [
                'notnull' => true,
                'length' => 36,
            ]);
            $table->addColumn('name', Types::STRING, [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('description', Types::TEXT, [
                'notnull' => false,
            ]);

            // Application specific fields
            $table->addColumn('type', Types::STRING, [
                'notnull' => true,
                'length' => 32,
                'comment' => 'Type of application (api-client, webhook-subscriber, etc.)',
            ]);
            $table->addColumn('configuration', Types::JSON, [
                'notnull' => false,
                'comment' => 'Configuration options for the specific type',
            ]);
            $table->addColumn('permissions', Types::JSON, [
                'notnull' => false,
                'comment' => 'Permission settings for the application',
            ]);

            // Authentication
            $table->addColumn('client_id', Types::STRING, [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('client_secret', Types::STRING, [
                'notnull' => true,
                'length' => 255,
            ]);

            // Webhook configuration
            $table->addColumn('webhooks', Types::JSON, [
                'notnull' => false,
                'comment' => 'Webhook configurations',
            ]);

            // Status
            $table->addColumn('is_enabled', Types::BOOLEAN, [
                'notnull' => true,
                'default' => true,
            ]);

            // Timestamps
            $table->addColumn('date_created', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('date_modified', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('last_access', Types::DATETIME, [
                'notnull' => false,
            ]);

            // Set primary key and indexes
            $table->setPrimaryKey(['id']);
            $table->addIndex(['uuid'], 'openconnector_app_uuid_idx');
            $table->addIndex(['client_id'], 'openconnector_app_client_id_idx');
            $table->addIndex(['type'], 'openconnector_app_type_idx');
            $table->addUniqueIndex(['uuid'], 'openconnector_app_uuid_unique');
            $table->addUniqueIndex(['client_id'], 'openconnector_app_client_id_unique');
        }

        return $schema;
    }
} 