<?php
/**
 * @var \Teleport\Action\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$query = $this->modx->newQuery(\MODX\Revolution\modCategory::class, $criteria, false);

$iterator = $this->modx->getIterator(\MODX\Revolution\modCategory::class, $query, false);
foreach ($iterator as $object) {
    /** @var \MODX\Revolution\modCategory $object */
    modcategory_populate_category_children($object);
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}

function modcategory_populate_category_children(\MODX\Revolution\modCategory &$object) {
    $children = $object->getMany('Children', null, false);
    if ($children) {
        /** @var \MODX\Revolution\modCategory $child */
        foreach ($children as &$child) {
            if ($child->get('id') == $object->get('id')) continue;
            modcategory_populate_category_children($child);
        }
    }
}
