<?php
/**
 * OpenConnector - Connect your Nextcloud to external services
 *
 * This migration changes the following:
 * - Renaming of SynchronizationContract sourceId & sourceHash to originId and originHash,
 *   creating the new columns and transferring old data to the new fields.
 * - Removal of old indexes related to sourceId and sourceHash
 * - Addition of new indexes for originId and synchronization_id fields
 *
 * @category  Migration
 * @package   OpenConnector
 * @author    Conduction Development Team <dev@conductio.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @version   GIT: <git-id>
 * @link      https://OpenConnector.app
 */

declare(strict_types=1);

namespace OCA\OpenConnector\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\IDBConnection;

/**
 * Migration class for renaming SynchronizationContract columns
 *
 * This migration renames the sourceId and sourceHash columns to originId and originHash
 * and updates the associated indexes.
 *
 * @package   OCA\OpenConnector\Migration
 * @category  Migration
 * @author    Conduction Development Team <dev@conductio.nl>
 * @copyright 2024 Conduction B.V.
 * @license   EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @version   1.0.0
 */
class Version1Date20241111144800 extends SimpleMigrationStep
{
    /**
     * Database connection
     *
     * @var IDBConnection
     */
    private IDBConnection $connection;

    /**
     * Constructor
     *
     * @param IDBConnection $connection Database connection
     * 
     * @return void
     */
    public function __construct(IDBConnection $connection)
    {
        $this->connection = $connection;
    }//end __construct()

    /**
     * Pre-schema change hook
     *
     * Actions to be executed before schema changes are applied
     *
     * @param IOutput                   $output        Output for the migration
     * @param Closure(): ISchemaWrapper $schemaClosure Closure that returns the schema
     * @param array                     $options       Options for the migration
     * 
     * @return void
     */
    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
    {
    }//end preSchemaChange()

    /**
     * Change database schema
     *
     * Adds new columns and updates indexes
     *
     * @param IOutput                   $output        Output for the migration
     * @param Closure(): ISchemaWrapper $schemaClosure Closure that returns the schema
     * @param array                     $options       Options for the migration
     * 
     * @return ISchemaWrapper|null Modified schema or null if no changes
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        /**
         * @var ISchemaWrapper $schema
         */
        $schema = $schemaClosure();
        $table  = $schema->getTable('openconnector_synchronization_contracts');

        // Step 1: Add new columns for 'origin_id' and 'origin_hash'.
        if ($table->hasColumn('origin_id') === false) {
            $table->addColumn(
                'origin_id',
                Types::STRING,
                [
                    'length'  => 255,
                    'notnull' => true,
                ]
            );
        }

        if ($table->hasColumn('origin_hash') === false) {
            $table->addColumn(
                'origin_hash',
                Types::STRING,
                [
                    'length'  => 255,
                    'notnull' => false,
                ]
            );
        }

        // Step 4: Adjust indexes in preparation for data migration.
        if ($table->hasIndex('openconnector_sync_contracts_source_id_index') === true) {
            $table->dropIndex('openconnector_sync_contracts_source_id_index');
        }

        if ($table->hasIndex('openconnector_sync_contracts_origin_id_index') === false) {
            $table->addIndex(['origin_id'], 'openconnector_sync_contracts_origin_id_index');
        }

        if ($table->hasIndex('openconnector_sync_contracts_sync_source_index') === true) {
            $table->dropIndex('openconnector_sync_contracts_sync_source_index');
        }

        if ($table->hasIndex('openconnector_sync_contracts_sync_origin_index') === false) {
            $table->addIndex(['synchronization_id', 'origin_id'], 'openconnector_sync_contracts_sync_origin_index');
        }

        return $schema;
    }//end changeSchema()

    /**
     * Post-schema change hook
     *
     * Copy data from old columns to new columns and drop old columns
     *
     * @param IOutput                   $output        Output for the migration
     * @param Closure(): ISchemaWrapper $schemaClosure Closure that returns the schema
     * @param array                     $options       Options for the migration
     * 
     * @return void
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
    {
        /**
         * @var ISchemaWrapper $schema
         */
        $schema = $schemaClosure();
        $table  = $schema->getTable('openconnector_synchronization_contracts');

        // Step 2: Copy data from old columns to new columns.
        if (($table->hasColumn('origin_id') === true) && ($table->hasColumn('origin_hash') === true)
            && ($table->hasColumn('source_id') === true) && ($table->hasColumn('source_hash') === true)
        ) {
            $this->connection->executeQuery(
                "
				UPDATE oc_openconnector_synchronization_contracts
				SET origin_id = source_id, origin_hash = source_hash
				WHERE source_id IS NOT NULL
			"
            );
        }

        if ($table->hasColumn('source_id') === true) {
            $table->dropColumn('source_id');
        }

        if ($table->hasColumn('source_hash') === true) {
            $table->dropColumn('source_hash');
        }
    }//end postSchemaChange()
}//end class
