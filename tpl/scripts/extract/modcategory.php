<?php
/**
 * @var \Teleport\Actions\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$query = $this->modx->newQuery('modCategory', $criteria, false);

$iterator = $this->modx->getIterator('modCategory', $query, false);
foreach ($iterator as $object) {
    /** @var modCategory $object */
    modcategory_populate_category_children($object);
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}

function modcategory_populate_category_children(modCategory &$object) {
    $children = $object->getMany('Children', null, false);
    if ($children) {
        foreach ($children as &$child) {
            if ($child->get('id') == $object->get('id')) continue;
            modcategory_populate_category_children($child);
        }
    }
}
