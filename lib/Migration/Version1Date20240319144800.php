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
 * Migration to create the authentication vault system:
 * - Creates new openconnector_auth table
 * - Adds auth_id column to openconnector_sources table
 * - Creates necessary indexes and foreign key constraints
 */
class Version1Date20240319144800 extends SimpleMigrationStep {

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
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // Create authentication table if it doesn't exist
        if (!$schema->hasTable('openconnector_auth')) {
            $table = $schema->createTable('openconnector_auth');
            
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

            // Authentication specific fields
            $table->addColumn('type', Types::STRING, [
                'notnull' => true,
                'length' => 32,
                'comment' => 'Type of authentication (apikey, jwt, etc.)',
            ]);
            $table->addColumn('configuration', Types::JSON, [
                'notnull' => false,
                'comment' => 'Configuration options for the specific type',
            ]);

            // Timestamps
            $table->addColumn('date_created', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('date_modified', Types::DATETIME, [
                'notnull' => true,
            ]);

            // Set primary key and indexes
            $table->setPrimaryKey(['id']);
            $table->addIndex(['uuid'], 'openconnector_auth_uuid_idx');
            $table->addIndex(['type'], 'openconnector_auth_type_idx');
            $table->addUniqueIndex(['uuid'], 'openconnector_auth_uuid_unique');
        }

        // Add auth_id to sources table if it doesn't exist
        $sourcesTable = $schema->getTable('openconnector_sources');
        if (!$sourcesTable->hasColumn('auth_id')) {
            $sourcesTable->addColumn('auth_id', Types::BIGINT, [
                'notnull' => false,
                'length' => 20,
                'comment' => 'Foreign key to authentication configuration',
            ]);

            // Add index and foreign key constraint
            $sourcesTable->addIndex(['auth_id'], 'openconnector_sources_auth_idx');
            $sourcesTable->addForeignKeyConstraint(
                'openconnector_auth',
                ['auth_id'],
                ['id'],
                [
                    'onDelete' => 'SET NULL',
                    'onUpdate' => 'CASCADE',
                ],
                'fk_sources_auth'
            );
        }

        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
        // Migrate existing authentication data from sources to new auth table
        $qb = $this->connection->getQueryBuilder();
        
        // Get all sources with authentication data
        $sources = $qb->select('id', 'auth', 'authentication_config')
            ->from('openconnector_sources')
            ->where($qb->expr()->isNotNull('auth'))
            ->execute()
            ->fetchAll();

        foreach ($sources as $source) {
            if (empty($source['auth']) || empty($source['authentication_config'])) {
                continue;
            }

            // Create new auth entry
            $insertQb = $this->connection->getQueryBuilder();
            $insertQb->insert('openconnector_auth')
                ->values([
                    'uuid' => $insertQb->createNamedParameter(\Symfony\Component\Uid\Uuid::v4()->toString()),
                    'name' => $insertQb->createNamedParameter('Migrated: ' . $source['auth']),
                    'type' => $insertQb->createNamedParameter($source['auth']),
                    'configuration' => $insertQb->createNamedParameter($source['authentication_config'], Types::JSON),
                    'date_created' => $insertQb->createNamedParameter(new \DateTime(), Types::DATETIME),
                    'date_modified' => $insertQb->createNamedParameter(new \DateTime(), Types::DATETIME),
                ])
                ->execute();

            $authId = $this->connection->lastInsertId();

            // Update source with new auth_id
            $updateQb = $this->connection->getQueryBuilder();
            $updateQb->update('openconnector_sources')
                ->set('auth_id', $updateQb->createNamedParameter($authId))
                ->where($updateQb->expr()->eq('id', $updateQb->createNamedParameter($source['id'])))
                ->execute();
        }
    }
} 