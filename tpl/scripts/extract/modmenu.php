<?php
/**
 * @var \Teleport\Action\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$query = $this->modx->newQuery(\MODX\Revolution\modMenu::class, $criteria, false);

$iterator = $this->modx->getIterator(\MODX\Revolution\modMenu::class, $query, false);
foreach ($iterator as $object) {
    /** @var \MODX\Revolution\modMenu $object */
    modmenu_populate_menu_action($object);
    modmenu_populate_menu_children($object);
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}
function modmenu_populate_menu_action(\MODX\Revolution\modMenu &$object) {
    $action = $object->getOne('Action', null, false);
    if ($action) {
        $object->Action->getMany('Fields', null, false);
    }
}
function modmenu_populate_menu_children(\MODX\Revolution\modMenu &$object) {
    $children = $object->getMany('Children', null, false);
    if ($children) {
        /** @var \MODX\Revolution\modMenu $child */
        foreach ($children as &$child) {
            if ($child->get('text') == $object->get('text')) continue;
            modmenu_populate_menu_action($child);
            modmenu_populate_menu_children($child);
        }
    }
}
