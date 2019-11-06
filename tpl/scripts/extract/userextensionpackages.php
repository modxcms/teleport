<?php
/**
 * @var Teleport\Action\Extract $this
 * @var array $graph
 * @var array $graphCriteria
 * @var array $vehicle
 * @var integer $vehicleCount
 */
$criteria = isset($vehicle['object']['criteria']) ? $vehicle['object']['criteria'] : null;
$object = $this->modx->getObject(\MODX\Revolution\modSystemSetting::class, $criteria);
if ($object) {
    $extPackages = $object->get('value');
    $extPackages = $this->modx->fromJSON($extPackages);
    if (is_array($extPackages) && !empty($extPackages)) {
        $extUserPackages = array();
        $extUsers = $this->modx->getIterator(\MODX\Revolution\modUser::class, array('class_key:!=' => \MODX\Revolution\modUser::class));
        /** @var \MODX\Revolution\modUser $user */
        foreach ($extUsers as $user) {
            $extUserClass = $user->_class;
            $extUserPkg = $user->_package;
            if (!array_key_exists($extUserPkg, $extUserPackages) && array_key_exists($extUserPkg, $extPackages)) {
                $extUserPackages[$extUserPkg] = $extPackages[$extUserPkg];
            }
        }
        if (!empty($extUserPackages)) {
            foreach ($extUserPackages as $pkgKey => $pkg) {
                if (array_key_exists($pkgKey, $this->modx->packages) && isset($this->modx->packages[$pkgKey]['path'])) {
                    $pkgModelDir = $this->modx->packages[$pkgKey]['path'];
                    $source = realpath(dirname($pkgModelDir));
                    $target = 'MODX_CORE_PATH . "components/"';
                    if (strpos($source, realpath(MODX_CORE_PATH)) === 0) {
                        $target = 'MODX_CORE_PATH . "' . str_replace(realpath(MODX_CORE_PATH), '', $source) . '"';
                    } elseif (strpos($source, realpath(MODX_BASE_PATH)) === 0) {
                        $target = 'MODX_BASE_PATH . "' . str_replace(realpath(MODX_BASE_PATH), '', $source) . '"';
                    }
                    if (!isset($vehicle['attributes']['validate'])) $vehicle['attributes']['validate'] = array();
                    $vehicle['attributes']['validate'][] = array(
                        'type' => 'file',
                        'source' => $source,
                        'target' => 'return ' . $target . ';'
                    );
                }
            }
            $object->set('value', $this->modx->toJSON($extUserPackages));
            if ($this->package->put($object, $vehicle['attributes'])) {
                $vehicleCount++;
            }
        }
    }
}
