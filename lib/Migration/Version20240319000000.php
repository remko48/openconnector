<?php

declare(strict_types=1);

namespace OCA\OpenConnector\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migratie voor het aanmaken van de authenticatie tabel
 */
class Version20240319000000 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('openconnector_auth')) {
            $table = $schema->createTable('openconnector_auth');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('uuid', 'string', [
                'notnull' => true,
                'length' => 36,
            ]);
            $table->addColumn('name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('description', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('type', 'string', [
                'notnull' => true,
                'length' => 32,
            ]);
            $table->addColumn('configuration', 'json', [
                'notnull' => false,
            ]);
            $table->addColumn('date_created', 'datetime', [
                'notnull' => true,
            ]);
            $table->addColumn('date_modified', 'datetime', [
                'notnull' => true,
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['uuid'], 'openconnector_auth_uuid_idx');
            $table->addIndex(['type'], 'openconnector_auth_type_idx');
        }

        // Add auth_id column to sources table if it doesn't exist
        $sourcesTable = $schema->getTable('openconnector_sources');
        if (!$sourcesTable->hasColumn('auth_id')) {
            $sourcesTable->addColumn('auth_id', 'integer', [
                'notnull' => false,
            ]);
            $sourcesTable->addIndex(['auth_id'], 'openconnector_sources_auth_idx');
            $sourcesTable->addForeignKeyConstraint(
                'openconnector_auth',
                ['auth_id'],
                ['id'],
                ['onDelete' => 'SET NULL']
            );
        }

        return $schema;
    }
} 