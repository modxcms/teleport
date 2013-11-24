<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Action;

use Teleport\Parser\Parser;
use Teleport\Transport\Transport;

/**
 * Extract a Snapshot from a MODX Instance.
 *
 * @property \stdClass profile
 * @property array     tpl
 * @property string    target
 * @property string    push
 * @property bool      preserveWorkspace
 *
 * @package Teleport\Action
 */
class Extract extends Action
{
    /**
     * @var Transport The transport package Extracted from the \modX instance.
     */
    public $package;
    /**
     * @var array Defines the arguments required for the Push action.
     */
    protected $required = array('profile', 'tpl');
    /**
     * @var string Defines the base path of the tpl.
     */
    private $tplBase;

    /**
     * Process the Extract action.
     *
     * @throws ActionException If an error is encountered during processing.
     */
    public function process()
    {
        parent::process();
        try {
            $this->profile = $this->loadProfile($this->profile);
            $this->tpl = $this->loadTpl($this->tpl);

            $this->getMODX();

            $this->prepareTpl();

            $this->package = $this->createPackage($this->profile->code . '_' . $this->tpl['name'], $this->getVersion(), $this->getSequence());

            foreach ($this->tpl['vehicles'] as $vehicle) {
                $this->createVehicles($vehicle);
            }

            if (!$this->package->pack()) {
                throw new ActionException($this, "Error packing {$this->package->signature}.transport.zip");
            }
            $this->request->log("Successfully extracted {$this->package->signature}.transport.zip from instance {$this->profile->code}");
            if ($this->target && $this->push) {
                if (!$this->push($this->package->path . $this->package->signature . '.transport.zip', $this->target . $this->package->signature . '.transport.zip')) {
                    throw new ActionException($this, "Error pushing {$this->package->signature}.transport.zip to {$this->target}");
                }

                if (!$this->preserveWorkspace && $this->modx->getCacheManager()) {
                    $this->modx->cacheManager->deleteTree($this->package->path . $this->package->signature);
                    @unlink($this->package->path . $this->package->signature . '.transport.zip');
                }

                $this->request->log("Successfully pushed {$this->package->signature}.transport.zip to {$this->target}");
                $this->request->log("{$this->target}{$this->package->signature}.transport.zip", false);
            } else {
                $this->request->log("{$this->package->path}{$this->package->signature}.transport.zip", false);
            }
        } catch (\Exception $e) {
            throw new ActionException($this, "Error Extracting snapshot: " . $e->getMessage(), $e);
        }
    }

    /**
     * Load the JSON extract tpl data into a PHP array.
     *
     * @param string $tpl A valid stream or file location for the tpl.
     *
     * @return array An array of the tpl data.
     */
    protected function loadTpl($tpl)
    {
        $this->tplBase = dirname($tpl);
        return json_decode(file_get_contents($tpl), true);
    }

    /**
     * Parse the tpl replacing placeholders from the profile and request arguments.
     */
    protected function prepareTpl()
    {
        $this->modx->loadClass('modParser', '', false, true);
        $parser = new Parser($this->modx);
        $this->modx->toPlaceholders($this->profile);
        $this->modx->toPlaceholders($this->request->args());
        $tpl = $this->tpl;
        array_walk_recursive($tpl, function (&$value, $key, Parser $parser) {
            if (is_string($value)) {
                $parser->processElementTags('', $value);
            }
        }, $parser);
        $this->tpl = $tpl;
    }

    /**
     * Create a Transport package to store extracted data into.
     *
     * @param string $name The name of the package to create.
     * @param string $version The version of the package to create.
     * @param string $release The release of the package to create.
     *
     * @return Transport A new Transport instance with a signature compiled from name, version, and release.
     */
    protected function createPackage($name, $version, $release = '')
    {
        $this->modx->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);
        $this->modx->loadClass('transport.xPDOVehicle', XPDO_CORE_PATH, true, true);
        $this->modx->loadClass('transport.xPDOObjectVehicle', XPDO_CORE_PATH, true, true);

        /* setup the signature and filename */
        $s['name'] = strtolower($name);
        $s['version'] = $version;
        $s['release'] = $release;
        $signature = $s['name'];
        if (!empty ($s['version'])) {
            $signature .= '-' . $s['version'];
        }
        if (!empty ($s['release'])) {
            $signature .= '-' . $s['release'];
        }
        $filename = $signature . '.transport.zip';

        /* remove the package if it's already been made */
        $directory = TELEPORT_BASE_PATH . 'workspace/';
        if (file_exists($directory . $filename)) {
            unlink($directory . $filename);
        }
        if (file_exists($directory . $signature) && is_dir($directory . $signature)) {
            $this->modx->getCacheManager()->deleteTree($directory . $signature, array('deleteTop' => false, 'skipDirs' => false, 'extensions' => array()));
        }
        if (!file_exists($directory)) {
            $this->modx->getCacheManager()->writeTree($directory);
        }

        /* create the transport package */
        $this->package = new Transport($this->modx, $signature, $directory);
        $this->request->log("Created new transport package with signature: {$signature}");

        return $this->package;
    }

    /**
     * Get a package version for this snapshot.
     *
     * @return string The package version string.
     */
    protected function getVersion()
    {
        return strftime('%y%m%d.%H%M.%S');
    }

    /**
     * Get a package sequence for this snapshot.
     *
     * @return string The package sequence string.
     */
    protected function getSequence()
    {
        return $this->modx->version['full_version'];
    }

    /**
     * Create \xPDOVehicle instances from MODX assets and data to go into the Snapshot.
     *
     * @param object $vehicle A vehicle definition from the Extract tpl being applied.
     *
     * @return int The number of vehicles created from the definition.
     */
    protected function createVehicles($vehicle)
    {
        $vehicleCount = 0;
        if (isset($vehicle['attributes']['validate'])) {
            foreach ($vehicle['attributes']['validate'] as &$validator) {
                if ($validator['type'] === 'php') {
                    if (strpos($validator['source'], ':/') === false && strpos($validator['source'], '/') !== 0) {
                        $validator['source'] = $this->tplBase . '/' . $validator['source'];
                    }
                }
            }
        }
        if (isset($vehicle['attributes']['resolve'])) {
            foreach ($vehicle['attributes']['resolve'] as &$resolver) {
                if ($resolver['type'] === 'php') {
                    if (strpos($resolver['source'], ':/') === false && strpos($resolver['source'], '/') !== 0) {
                        $resolver['source'] = $this->tplBase . '/' . $resolver['source'];
                    }
                }
            }
        }
        switch ($vehicle['vehicle_class']) {
            case '\\Teleport\\Transport\\xPDOObjectVehicle':
            case 'xPDOObjectVehicle':
                $realClass = $this->modx->loadClass($vehicle['object']['class']);
                $graph = isset($vehicle['object']['graph']) && is_array($vehicle['object']['graph'])
                    ? $vehicle['object']['graph'] : array();
                $graphCriteria = isset($vehicle['object']['graphCriteria']) && is_array($vehicle['object']['graphCriteria'])
                    ? $vehicle['object']['graphCriteria'] : null;
                if (isset($vehicle['object']['script'])) {
                    include $this->tplBase . '/scripts/' . $vehicle['object']['script'];
                } elseif (isset($vehicle['object']['criteria'])) {
                    $iterator = $this->modx->getIterator($vehicle['object']['class'], (array)$vehicle['object']['criteria'], false);
                    foreach ($iterator as $object) {
                        /** @var \xPDOObject $object */
                        if (!empty($graph)) {
                            $object->getGraph($graph, $graphCriteria, false);
                        }
                        if ($this->package->put($object, $vehicle['attributes'])) {
                            $vehicleCount++;
                        }
                    }
                } elseif (isset($vehicle['object']['data'])) {
                    /** @var \xPDOObject $object */
                    $object = $this->modx->newObject($vehicle['object']['class']);
                    if ($object instanceof $realClass) {
                        $object->fromArray($vehicle['object']['data'], '', true, true);
                        if ($this->package->put($object, $vehicle['attributes'])) {
                            $vehicleCount++;
                        }
                    }
                }
                $this->request->log("Packaged {$vehicleCount} xPDOObjectVehicles for class {$vehicle['object']['class']}");
                break;
            case '\\Teleport\\Transport\\xPDOCollectionVehicle':
                $objCnt = 0;
                $realClass = $this->modx->loadClass($vehicle['object']['class']);
                $graph = isset($vehicle['object']['graph']) && is_array($vehicle['object']['graph'])
                    ? $vehicle['object']['graph'] : array();
                $graphCriteria = isset($vehicle['object']['graphCriteria']) && is_array($vehicle['object']['graphCriteria'])
                    ? $vehicle['object']['graphCriteria'] : null;
                if (isset($vehicle['object']['script'])) {
                    include $this->tplBase . '/scripts/' . $vehicle['object']['script'];
                } elseif (isset($vehicle['object']['criteria'])) {
                    $limit = isset($vehicle['object']['limit']) ? (integer)$vehicle['object']['limit'] : 0;
                    if ($limit < 1) {
                        $limit = 500;
                    }
                    $offset = 0;
                    $criteria = $this->modx->newQuery($vehicle['object']['class'], (array)$vehicle['object']['criteria'], false);
                    $set = $this->modx->getCollection($vehicle['object']['class'], $criteria->limit($limit, $offset), false);
                    while (!empty($set)) {
                        foreach ($set as &$object) {
                            /** @var \xPDOObject $object */
                            if (!empty($graph)) {
                                $object->getGraph($graph, $graphCriteria, false);
                            }
                        }
                        if (!empty($set) && $this->package->put($set, $vehicle['attributes'])) {
                            $vehicleCount++;
                            $objCnt = $objCnt + count($set);
                        }
                        $offset += $limit;
                        $set = $this->modx->getCollection($vehicle['object']['class'], $criteria->limit($limit, $offset), false);
                    }
                }
                $this->request->log("Packaged {$vehicleCount} xPDOCollectionVehicles with {$objCnt} total objects for class {$vehicle['object']['class']}");
                break;
            case '\\Teleport\\Transport\\MySQLVehicle':
                /* collect table names from classes and grab any additional tables/data not listed */
                $modxDatabase = $this->modx->getOption('dbname', null, $this->modx->getOption('database'));
                $modxTablePrefix = $this->modx->getOption('table_prefix', null, '');

                $coreTables = array();
                foreach ($vehicle['object']['classes'] as $class) {
                    $coreTables[$class] = $this->modx->quote($this->modx->literal($this->modx->getTableName($class)));
                }

                $stmt = $this->modx->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$modxDatabase}' AND TABLE_NAME NOT IN (" . implode(',', $coreTables) . ")");
                $extraTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                if (is_array($extraTables) && !empty($extraTables)) {
                    $excludeExtraTablePrefix = isset($vehicle['object']['excludeExtraTablePrefix']) && is_array($vehicle['object']['excludeExtraTablePrefix'])
                        ? $vehicle['object']['excludeExtraTablePrefix'] : array();
                    $excludeExtraTables = isset($vehicle['object']['excludeExtraTables']) && is_array($vehicle['object']['excludeExtraTables'])
                        ? $vehicle['object']['excludeExtraTables'] : array();
                    foreach ($extraTables as $extraTable) {
                        if (in_array($extraTable, $excludeExtraTables)) continue;

                        $instances = 0;
                        $object = array(
                            'vehicle_package' => '',
                            'vehicle_class' => '\\Teleport\\Transport\\MySQLVehicle'
                        );
                        $attributes = array(
                            'vehicle_package' => '',
                            'vehicle_class' => '\\Teleport\\Transport\\MySQLVehicle'
                        );

                        /* remove modx table_prefix if table starts with it */
                        $extraTableName = $extraTable;
                        if (!empty($modxTablePrefix) && strpos($extraTableName, $modxTablePrefix) === 0) {
                            $extraTableName = substr($extraTableName, strlen($modxTablePrefix));
                            $addTablePrefix = true;
                        } elseif (!empty($modxTablePrefix) || in_array($extraTableName, $excludeExtraTablePrefix)) {
                            $addTablePrefix = false;
                        } else {
                            $addTablePrefix = true;
                        }
                        $object['tableName'] = $extraTableName;
                        $this->request->log("Extracting non-core table {$extraTableName}");

                        /* generate the CREATE TABLE statement */
                        $stmt = $this->modx->query("SHOW CREATE TABLE {$this->modx->escape($extraTable)}");
                        $resultSet = $stmt->fetch(\PDO::FETCH_NUM);
                        $stmt->closeCursor();
                        if (isset($resultSet[1])) {
                            if ($addTablePrefix) {
                                $object['drop'] = "DROP TABLE IF EXISTS {$this->modx->escape('[[++table_prefix]]' . $extraTableName)}";
                                $object['table'] = str_replace("CREATE TABLE {$this->modx->escape($extraTable)}", "CREATE TABLE {$this->modx->escape('[[++table_prefix]]' . $extraTableName)}", $resultSet[1]);
                            } else {
                                $object['drop'] = "DROP TABLE IF EXISTS {$this->modx->escape($extraTableName)}";
                                $object['table'] = $resultSet[1];
                            }

                            /* collect the rows and generate INSERT statements */
                            $object['data'] = array();
                            $stmt = $this->modx->query("SELECT * FROM {$this->modx->escape($extraTable)}");
                            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                                if ($instances === 0) {
                                    $fields = implode(', ', array_map(array($this->modx, 'escape'), array_keys($row)));
                                }
                                $values = array();
                                while (list($key, $value) = each($row)) {
                                    switch (gettype($value)) {
                                        case 'string':
                                            $values[] = $this->modx->quote($value);
                                            break;
                                        case 'NULL':
                                        case 'array':
                                        case 'object':
                                        case 'resource':
                                        case 'unknown type':
                                            $values[] = 'NULL';
                                            break;
                                        default:
                                            $values[] = (string)$value;
                                            break;
                                    }
                                }
                                $values = implode(', ', $values);
                                if ($addTablePrefix) {
                                    $object['data'][] = "INSERT INTO {$this->modx->escape('[[++table_prefix]]' . $extraTableName)} ({$fields}) VALUES ({$values})";
                                } else {
                                    $object['data'][] = "INSERT INTO {$this->modx->escape($extraTable)} ({$fields}) VALUES ({$values})";
                                }
                                $instances++;
                            }
                        }

                        if (!$this->package->put($object, $attributes)) {
                            $this->request->log("Could not package rows for table {$extraTable}");
                        } else {
                            $this->request->log("Packaged {$instances} rows for non-core table {$extraTable}");
                            $vehicleCount++;
                        }
                    }
                    $this->request->log("Packaged {$vehicleCount} {$vehicle['vehicle_class']} vehicles for non-core tables");
                } else {
                    $this->request->log("No non-core tables found for packaging");
                }
                break;
            case 'xPDOScriptVehicle':
                if (isset($vehicle['object']['source'])) {
                    if (strpos($vehicle['object']['source'], ':/') === false && strpos($vehicle['object']['source'], '/') !== 0) {
                        $vehicle['object']['source'] = $this->tplBase . '/' . $vehicle['object']['source'];
                    }
                }
            case 'xPDOFileVehicle':
            case 'xPDOTransportVehicle':
            default:
                if (isset($vehicle['object']['script'])) {
                    include $this->tplBase . '/scripts/' . $vehicle['object']['script'];
                } else {
                    if ($this->package->put($vehicle['object'], $vehicle['attributes'])) {
                        $this->request->log("Packaged 1 {$vehicle['vehicle_class']}" . (isset($vehicle['object']['source'])
                                ? " from {$vehicle['object']['source']}" : ""));
                        $vehicleCount++;
                    }
                }
                break;
        }
        return $vehicleCount;
    }
} 
