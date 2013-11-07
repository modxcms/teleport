<?php
/**
 * @var \Teleport\Action\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$graph = isset($vehicle['object']['graph']) ? $vehicle['object']['graph'] : array();
$graphCriteria = isset($vehicle['object']['graphCriteria']) ? $vehicle['object']['graphCriteria'] : null;
$query = $this->modx->newQuery('modAction', $criteria, false);

$where = array(
    "NOT EXISTS (SELECT 1 FROM {$this->modx->getTableName('modMenu')} menu WHERE menu.action = modAction.id)"
);
$query->where($where);

$iterator = $this->modx->getIterator('modAction', $query, false);
foreach ($iterator as $object) {
    /** @var xPDOObject $object */
    if (!empty($graph)) {
        $object->getGraph($graph, $graphCriteria, false);
    }
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}
