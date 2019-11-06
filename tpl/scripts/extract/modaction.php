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
$query = $this->modx->newQuery(\MODX\Revolution\modAction::class, $criteria, false);

$where = array(
    "NOT EXISTS (SELECT 1 FROM {$this->modx->getTableName(\MODX\Revolution\modMenu::class)} menu WHERE menu.action = modAction.id)"
);
$query->where($where);

$iterator = $this->modx->getIterator(\MODX\Revolution\modAction::class, $query, false);
foreach ($iterator as $object) {
    /** @var \xPDO\Om\xPDOObject $object */
    if (!empty($graph)) {
        $object->getGraph($graph, $graphCriteria, false);
    }
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}
