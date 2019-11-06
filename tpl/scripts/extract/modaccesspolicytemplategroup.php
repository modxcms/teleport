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
$query = $this->modx->newQuery(\MODX\Revolution\modAccessPolicyTemplateGroup::class, $criteria, false);

$iterator = $this->modx->getIterator(\MODX\Revolution\modAccessPolicyTemplateGroup::class, $query, false);
foreach ($iterator as $object) {
    /** @var \MODX\Revolution\modAccessPolicyTemplateGroup $object */
    if (!empty($graph)) {
        $object->getGraph($graph, $graphCriteria, false);
    }
    $templates = $object->getMany('Templates');
    if (!empty($templates)) {
        foreach ($templates as &$template) {
            /** @var \MODX\Revolution\modAccessPolicyTemplate $template */
            $template->getGraph(array('Permissions' => array(), 'Policies' => array()), null, false);
            $policies = $template->getMany('Policies');
            if (!empty($policies)) {
                foreach ($policies as &$policy) {
                    /** @var \MODX\Revolution\modAccessPolicy $policy */
                    modaccesspolicytemplategroup_populate_policy_children($policy);
                }
            }
        }
    }
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}

function modaccesspolicytemplategroup_populate_policy_children(\MODX\Revolution\modAccessPolicy &$object) {
    $children = $object->getMany('Children', null, false);
    if ($children) {
        /** @var \MODX\Revolution\modAccessPolicy $child */
        foreach ($children as &$child) {
            if ($child->get('id') == $object->get('id')) continue;
            modaccesspolicytemplategroup_populate_policy_children($child);
        }
    }
}
