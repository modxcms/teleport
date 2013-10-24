<?php
/**
 * @var \Teleport\Actions\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$class = $vehicle['object']['class'];
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$graph = isset($vehicle['object']['graph']) ? $vehicle['object']['graph'] : array();
$graphCriteria = isset($vehicle['object']['graphCriteria']) ? $vehicle['object']['graphCriteria'] : null;
$query = $this->modx->newQuery($class, $criteria, false);

$iterator = $this->modx->getIterator($class, $query, false);
foreach ($iterator as $object) {
    /** @var xPDOObject $object */
    $principal = $this->modx->getObject($object->get('principal_class'), $object->get('principal'), false);
    $object->_relatedObjects['Principal'] = $principal;
    if (!empty($graph)) {
        $object->getGraph($graph, $graphCriteria, false);
    }
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}
