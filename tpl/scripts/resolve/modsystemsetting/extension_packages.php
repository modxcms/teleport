<?php
/**
 * @var \Teleport\Transport\Transport $transport
 * @var \modSystemSetting $object
 * @var array $options
 */
if ($object instanceof modSystemSetting && $object->get('key') === 'extension_packages') {
    $extPackages = $object->get('value');
    $extPackages = $transport->xpdo->fromJSON($extPackages);
    if (!is_array($extPackages)) $extPackages = array();
    if (is_array($options) && array_key_exists('extension_packages', $options)) {
        $optPackages = $transport->xpdo->fromJSON($options['extension_packages']);
        if (is_array($optPackages)) {
            $extPackages = array_merge($extPackages, $optPackages);
        }
    }
    if (!empty($extPackages)) {
        foreach ($extPackages as $extPackage) {
            if (!is_array($extPackage)) continue;

            foreach ($extPackage as $packageName => $package) {
                if (!empty($package) && !empty($package['path'])) {
                    $package['tablePrefix'] = !empty($package['tablePrefix']) ? $package['tablePrefix'] : null;
                    $package['path'] = str_replace(array(
                        '[[++core_path]]',
                        '[[++base_path]]',
                        '[[++assets_path]]',
                        '[[++manager_path]]',
                    ),array(
                        $transport->xpdo->config['core_path'],
                        $transport->xpdo->config['base_path'],
                        $transport->xpdo->config['assets_path'],
                        $transport->xpdo->config['manager_path'],
                    ),$package['path']);
                    $transport->xpdo->addPackage($packageName,$package['path'],$package['tablePrefix']);
                    if (!empty($package['serviceName']) && !empty($package['serviceClass'])) {
                        $packagePath = str_replace('//','/',$package['path'].$packageName.'/');
                        $transport->xpdo->getService($package['serviceName'],$package['serviceClass'],$packagePath);
                    }
                }
            }
        }
    }
}
