<?php
/**
 * @var Teleport\Action\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$this->modx->loadClass('transport.modTransportPackage');
foreach ($this->modx->getIterator('modWorkspace') as $workspace) {
    $packagesDir = $workspace->get('path') . 'packages/';
    $this->request->log("Packaging transport packages for workspace {$workspace->get('name')} using dir {$packagesDir}");
    $response = $this->modx->call('modTransportPackage', 'listPackages', array(&$this->modx, $workspace->get('id')));
    if (isset($response['collection'])) {
        foreach ($response['collection'] as $object) {
            $attributes = $vehicle['attributes'];
            $pkgSource = $object->get('source');
            $folderPos = strrpos($pkgSource, '/');
            $sourceDir = $folderPos > 1 ? substr($pkgSource, 0, $folderPos + 1) : '';
            $source = realpath($packagesDir . $pkgSource);
            $target = 'MODX_CORE_PATH . "packages/' . $sourceDir . '"';
            if (!isset($attributes['resolve'])) $attributes['resolve'] = array();
            $attributes['resolve'][] = array(
                'type' => 'file',
                'source' => $source,
                'target' => 'return ' . $target . ';'
            );
            if (isset($vehicle['object']['install']) && !empty($vehicle['object']['install'])) {
                $attributes['resolve'][] = array(
                    'type' => 'php',
                    'source' => TELEPORT_BASE_PATH . 'tpl/scripts/resolve/modtransportpackage/install.php'
                );
            }
            if ($this->package->put($object, $attributes)) {
                $this->request->log("Packaged modTransportPackage {$object->get('signature')} with file {$source}");
                $vehicleCount++;
            }
        }
    }
}
