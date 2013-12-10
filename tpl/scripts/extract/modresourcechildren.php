<?php
/**
 * @var \Teleport\Action\Extract $this
 * @var array $criteria
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$graph = isset($vehicle['object']['graph']) ? $vehicle['object']['graph'] : null;
$graphCriteria = isset($vehicle['object']['graphCriteria']) ? $vehicle['object']['graphCriteria'] : null;

$query = $this->modx->newQuery('modResource', $criteria, false);

$iterator = $this->modx->getIterator('modResource', $query, false);
foreach ($iterator as $object) {
    /** @var modResource $object */
    if ($graph !== null) {
        $object->getGraph($graph, $graphCriteria, false);
    }
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
    modresource_populate_children($this, $object, $criteria, $graph, $graphCriteria, $vehicle, $vehicleCount);
}
function modresource_populate_children(\Teleport\Action\Extract &$extract, modResource &$object, $criteria, $graph, $graphCriteria, $vehicle, &$vehicleCount) {
    unset($criteria['parent']);
    $children = $object->getMany('Children', null, false);
    if ($children) {
        foreach ($children as &$child) {
            /** @var modResource $child */
            if ($graph !== null) {
                $child->getGraph($graph, $graphCriteria);
            }
            if ($extract->package->put($child, $vehicle['attributes'])) {
                $vehicleCount++;
            }
            modresource_populate_children($extract, $child, $criteria, $graph, $graphCriteria, $vehicle, $vehicleCount);
        }
    }
}
