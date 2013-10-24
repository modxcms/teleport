<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Transport;

/**
 * A custom \xPDOVehicle implementation for generic MySQL tables and data.
 *
 * @package Teleport\Transport
 */
class MySQLVehicle extends \xPDOVehicle {
    public $class = 'MySQLVehicle';

    /**
     * Put a representation of a MySQL table and it's data into this vehicle.
     *
     * @param \xPDOTransport $transport The transport package hosting the vehicle.
     * @param mixed &$object A reference to the artifact this vehicle will represent.
     * @param array $attributes Additional attributes represented in the vehicle.
     */
    public function put(& $transport, & $object, $attributes = array ()) {
        $this->payload['class'] = $this->class;
        if (is_array($object) && isset($object['table']) && isset($object['tableName'])) {
            $this->payload['object'] = $object;
        }
        parent :: put($transport, $object, $attributes);
    }

    /**
     * Install the vehicle artifact into a transport host.
     *
     * @param \xPDOTransport &$transport A reference to the transport.
     * @param array $options An array of options for altering the installation of the artifact.
     * @return boolean True if the installation of the vehicle artifact was successful.
     */
    public function install(& $transport, $options) {
        $installed = false;
        $vOptions = $this->get($transport, $options);
        if (isset($vOptions['object']) && isset($vOptions['object']['tableName']) && isset($vOptions['object']['table']) && isset($vOptions['object']['data'])) {
            $tableName = $vOptions['object']['tableName'];
            /* attempt to execute the drop table if exists script */
            $dropTableQuery = isset($vOptions['object']['drop']) && !empty($vOptions['object']['drop'])
                ? $vOptions['object']['drop']
                : "DROP TABLE IF EXISTS {$transport->xpdo->escape('[[++table_prefix]]' . $tableName)}";
            $tableDropped = $transport->xpdo->exec(str_replace("[[++table_prefix]]", $transport->xpdo->getOption('table_prefix', $options, ''), $dropTableQuery));
            if ($tableDropped === false) {
                $transport->xpdo->log(\xPDO::LOG_LEVEL_WARN, "Error executing drop table script for table {$tableName}:\n{$dropTableQuery}");
            }
            /* attempt to execute the table creation script */
            $tableCreationQuery = str_replace("[[++table_prefix]]", $transport->xpdo->getOption('table_prefix', $options, ''), $vOptions['object']['table']);
            $tableCreated = $transport->xpdo->exec($tableCreationQuery);
            if ($tableCreated !== false) {
                /* insert data rows into the table */
                if (is_array($vOptions['object']['data'])) {
                    $rowsCreated = 0;
                    foreach ($vOptions['object']['data'] as $idx => $row) {
                        $insertResult = $transport->xpdo->exec(str_replace("[[++table_prefix]]", $transport->xpdo->getOption('table_prefix', $options, ''), $row));
                        if ($insertResult === false) {
                            $transport->xpdo->log(\xPDO::LOG_LEVEL_INFO, "Error inserting row {$idx} into table {$tableName}: " . print_r($transport->xpdo->errorInfo(), true));
                        } else {
                            $rowsCreated++;
                        }
                    }
                    $transport->xpdo->log(\xPDO::LOG_LEVEL_INFO, "Inserted {$rowsCreated} rows into table {$tableName}");
                }
            } else {
                $transport->xpdo->log(\xPDO::LOG_LEVEL_ERROR, "Could not create table {$tableName}: " . print_r($transport->xpdo->errorInfo(), true));
            }
        }
        return $installed;
    }

    /**
     * This vehicle implementation does not support uninstall.
     *
     * @param \xPDOTransport &$transport A reference to the transport.
     * @param array $options An array of options for altering the uninstallation of the artifact.
     * @return boolean True, always.
     */
    public function uninstall(& $transport, $options) {
        return true;
    }
}
