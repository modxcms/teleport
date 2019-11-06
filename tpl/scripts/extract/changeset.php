<?php
/**
 * @var \Teleport\Action\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
if (!isset($vehicle['object'])) {
    return;
}
$object = $vehicle['object'];
if (isset($object['changeset'])) {
    $changeSet = $object['changeset'];
    $removeRead = (isset($object['remove_read']) && !empty($object['remove_read'])) ? true : false;

    $GLOBALS['modx'] =& $this->modx;
    $this->modx->getService('registry', \MODX\Revolution\Registry\modRegistry::class);
    $this->modx->registry->getRegister('changes', \MODX\Revolution\Registry\modDbRegister::class, array('directory' => 'changes'));

    $this->modx->registry->changes->connect();
    $this->modx->registry->changes->subscribe("/{$changeSet}/");

    $changes = $this->modx->registry->changes->read(
        array(
            'remove_read' => $removeRead,
            'msg_limit' => 10000,
            'include_keys' => true
        )
    );
    $count = count($changes);

    if ($count > 0) {
        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_INFO, "Extracting changeset {$changeSet}: {$count} total changes");
        foreach ($changes as $key => $change) {
            $data = $this->modx->fromJSON($change);
            if (!isset($data['action']) || !isset($data['class'])) {
                $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, "changeset {$changeSet}: invalid data defined in change {$key}:\n" . print_r($data, true));
                continue;
            }
            switch ($data['action']) {
                case 'save':
                    /* create an xPDOObject vehicle */
                    $object = $this->modx->newObject($data['class']);
                    if (!$object) {
                        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, "Error getting instance of class {$data['class']}");
                        break;
                    }
                    $object->fromArray($data['object'], '', true, true);
                    if ($this->package->put(
                        $object,
                        array(
                            'vehicle_class' => \xPDO\Transport\xPDOObjectVehicle::class,
                            xPDOTransport::UPDATE_OBJECT => true,
                            xPDOTransport::PRESERVE_KEYS => true,
                        )
                    )) {
                        $vehicleCount++;
                    } else {
                        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, "Error creating vehicle for change {$key} — {$data['action']} - {$data['class']}::" . implode('-', (array)$data['pk']));
                    }
                    break;
                case 'remove':
                    /* add an xPDOScript vehicle to remove the object */
                    if ($this->package->put(
                        array(
                            'vehicle_class' => \xPDO\Transport\xPDOScriptVehicle::class,
                            'source' => 'tpl/scripts/remove.object.php',
                            'class' => $data['class'],
                            'pk' => $data['pk']
                        ),
                        array(
                            'vehicle_class' => \xPDO\Transport\xPDOScriptVehicle::class
                        )
                    )) {
                        $vehicleCount++;
                    } else {
                        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, "Error creating vehicle for change {$key} — {$data['action']} - {$data['class']}::" . implode('-', (array)$data['pk']));
                    }
                    break;
                case 'updateCollection':
                case 'removeCollection':
                    /* add an xPDOScript vehicle to execute an xPDOQuery object */
                    if ($this->package->put(
                        array(
                            'vehicle_class' => \xPDO\Transport\xPDOScriptVehicle::class,
                            'source' => 'tpl/scripts/exec.xpdoquery.php',
                            'class' => $data['class'],
                            'criteria' => $data['criteria']
                        ),
                        array(
                            'vehicle_class' => \xPDO\Transport\xPDOScriptVehicle::class
                        )
                    )) {
                        $vehicleCount++;
                    } else {
                        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, "Error creating vehicle for change {$key} - {$data['action']} - {$data['class']}");
                    }
                    break;
                default:
                    /* wth? */
                    $this->modx->log(modX::LOG_LEVEL_ERROR, "Changeset {$changeSet}: invalid action defined for change {$key}:\n" . print_r($data, true));
                    break;
            }
        }
    } else {
        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_INFO, "Changeset {$changeSet} is empty");
    }
}
