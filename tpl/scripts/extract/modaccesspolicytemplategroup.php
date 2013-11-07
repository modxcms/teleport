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
$query = $this->modx->newQuery('modAccessPolicyTemplateGroup', $criteria, false);

$iterator = $this->modx->getIterator('modAccessPolicyTemplateGroup', $query, false);
foreach ($iterator as $object) {
    /** @var modAccessPolicyTemplateGroup $object */
    if (!empty($graph)) {
        $object->getGraph($graph, $graphCriteria, false);
    }
    $templates = $object->getMany('Templates');
    if (!empty($templates)) {
        foreach ($templates as &$template) {
            /** @var modAccessPolicyTemplate $template */
            $template->getGraph(array('Permissions' => array(), 'Policies' => array()), null, false);
            $policies = $template->getMany('Policies');
            if (!empty($policies)) {
                foreach ($policies as &$policy) {
                    /** @var modAccessPolicy $policy */
                    modaccesspolicytemplategroup_populate_policy_children($policy);
                }
            }
        }
    }
    if ($this->package->put($object, $vehicle['attributes'])) {
        $vehicleCount++;
    }
}

function modaccesspolicytemplategroup_populate_policy_children(modAccessPolicy &$object) {
    $children = $object->getMany('Children', null, false);
    if ($children) {
        foreach ($children as &$child) {
            if ($child->get('id') == $object->get('id')) continue;
            modaccesspolicytemplategroup_populate_policy_children($child);
        }
    }
}
