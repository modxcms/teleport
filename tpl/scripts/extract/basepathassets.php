<?php
/**
 * @var \Teleport\Actions\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$excludes = array(
    '_build',
    'setup',
    'assets',
    'ht.access',
    'index.php',
    'config.core.php',
);
if (dirname(MODX_CORE_PATH) . '/' === MODX_BASE_PATH) $excludes[] = basename(MODX_CORE_PATH);
if (dirname(MODX_CONNECTORS_PATH) . '/' === MODX_BASE_PATH) $excludes[] = basename(MODX_CONNECTORS_PATH);
if (dirname(MODX_MANAGER_PATH) . '/' === MODX_BASE_PATH) $excludes[] = basename(MODX_MANAGER_PATH);
if (isset($vehicle['object']['excludes']) && is_array($vehicle['object']['excludes'])) {
    $excludes = array_unique($excludes + $vehicle['object']['excludes']);
}
if ($dh = opendir(MODX_BASE_PATH)) {
    $includes = array();
    while (($file = readdir($dh)) !== false) {
        /* ignore files/dirs starting with . or matching an exclude */
        if (strpos($file, '.') === 0 || in_array(strtolower($file), $excludes)) {
            continue;
        }
        $includes[] = array(
            'source' => MODX_BASE_PATH . $file,
            'target' => 'return MODX_BASE_PATH;'
        );
    }
    closedir($dh);
    foreach ($includes as $include) {
        if ($this->package->put($include, $vehicle['attributes'])) {
            $this->request->log("Packaged 1 {$vehicle['vehicle_class']} from {$include['source']}");
            $vehicleCount++;
        } else {
            $this->request->log("Error packaging {$vehicle['vehicle_class']} from {$include['source']}");
        }
    }
}
