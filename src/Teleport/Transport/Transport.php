<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Transport;


class Transport extends \xPDOTransport
{
    /** @var \modX */
    public $xpdo = null;

    /**
     * Get an existing \xPDOTransport instance.
     *
     * @param \modX &$xpdo A reference to a \modX instance.
     * @param string $source The source path to the transport.
     * @param string $target The target path to unpack the transport.
     * @param int $state The packed state of the transport.
     *
     * @return null|Transport|\xPDOTransport The transport instance or null.
     */
    public static function retrieve(& $xpdo, $source, $target, $state= self::STATE_PACKED) {
        $instance= null;
        $signature = basename($source, '.transport.zip');
        if (file_exists($source)) {
            if (is_writable($target)) {
                $manifest = self::unpack($xpdo, $source, $target, $state);
                if ($manifest) {
                    $instance = new Transport($xpdo, $signature, $target);
                    if (!$instance) {
                        $xpdo->log(\xPDO::LOG_LEVEL_ERROR, "Could not instantiate a valid TeleportTransport object from the package {$source} to {$target}. SIG: {$signature} MANIFEST: " . print_r($manifest, 1));
                    }
                    $manifestVersion = self::manifestVersion($manifest);
                    switch ($manifestVersion) {
                        case '0.1':
                            $instance->vehicles = self::_convertManifestVer1_1(self::_convertManifestVer1_0($manifest));
                        case '0.2':
                            $instance->vehicles = self::_convertManifestVer1_1(self::_convertManifestVer1_0($manifest[self::MANIFEST_VEHICLES]));
                            $instance->attributes = $manifest[self::MANIFEST_ATTRIBUTES];
                            break;
                        case '1.0':
                            $instance->vehicles = self::_convertManifestVer1_1($manifest[self::MANIFEST_VEHICLES]);
                            $instance->attributes = $manifest[self::MANIFEST_ATTRIBUTES];
                            break;
                        default:
                            $instance->vehicles = $manifest[self::MANIFEST_VEHICLES];
                            $instance->attributes = $manifest[self::MANIFEST_ATTRIBUTES];
                            break;
                    }
                } else {
                    $xpdo->log(\xPDO::LOG_LEVEL_ERROR, "Could not unpack package {$source} to {$target}. SIG: {$signature}");
                }
            } else {
                $xpdo->log(\xPDO::LOG_LEVEL_ERROR, "Could not unpack package: {$target} is not writable. SIG: {$signature}");
            }
        } else {
            $xpdo->log(\xPDO::LOG_LEVEL_ERROR, "Package {$source} not found. SIG: {$signature}");
        }
        return $instance;
    }

    public function get($objFile, $options= array ()) {
        $vehicle = null;
        $objFile = $this->path . $this->signature . '/' . $objFile;
        $vehiclePackage = isset($options['vehicle_package']) ? $options['vehicle_package'] : 'transport';
        $vehiclePackagePath = isset($options['vehicle_package_path']) ? $options['vehicle_package_path'] : '';
        $vehicleClass = isset($options['vehicle_class']) ? $options['vehicle_class'] : '';
        if (empty($vehicleClass)) $vehicleClass = $options['vehicle_class'] = 'xPDOObjectVehicle';
        if (!empty($vehiclePackage)) {
            $vehicleClass = "{$vehiclePackage}.{$vehicleClass}";
            $className = $this->xpdo->loadClass($vehicleClass, $vehiclePackagePath, true, true);
        } else {
            $className = $vehicleClass;
        }
        if ($className) {
            $vehicle = new $className();
            if (file_exists($objFile)) {
                $payload = include ($objFile);
                if ($payload) {
                    $vehicle->payload = $payload;
                }
            }
        } else {
            $this->xpdo->log(\xPDO::LOG_LEVEL_ERROR, "The specified xPDOVehicle class ({$vehicleClass}) could not be loaded.");
        }
        return $vehicle;
    }

    public function put($artifact, $attributes = array ()) {
        $added= false;
        if (!empty($artifact)) {
            $vehiclePackage = isset($attributes['vehicle_package']) ? $attributes['vehicle_package'] : 'transport';
            $vehiclePackagePath = isset($attributes['vehicle_package_path']) ? $attributes['vehicle_package_path'] : '';
            $vehicleClass = isset($attributes['vehicle_class']) ? $attributes['vehicle_class'] : '';
            if (empty($vehicleClass)) $vehicleClass = $options['vehicle_class'] = 'xPDOObjectVehicle';
            if (!empty($vehiclePackage)) {
                $vehicleClass = "{$vehiclePackage}.{$vehicleClass}";
                $className = $this->xpdo->loadClass($vehicleClass, $vehiclePackagePath, true, true);
            } else {
                $className = $vehicleClass;
            }
            if ($className) {
                /** @var \xPDOVehicle $vehicle */
                $vehicle = new $className();
                $vehicle->put($this, $artifact, $attributes);
                if ($added= $vehicle->store($this)) {
                    $this->registerVehicle($vehicle);
                }
            } else {
                $this->xpdo->log(\xPDO::LOG_LEVEL_ERROR, "The specified xPDOVehicle class ({$vehiclePackage}.{$vehicleClass}) could not be loaded.");
            }
        }
        return $added;
    }

    public function preInstall() {
        /* filter problem vehicles */
        $this->vehicles = array_filter($this->vehicles, function($vehicle) {
            $keep = true;
            switch ($vehicle['vehicle_class']) {
                case 'xPDOObjectVehicle':
                    switch ($vehicle['class']) {
                        case 'modSystemSetting':
                            $excludes = array(
                                'session_cookie_domain',
                                'session_cookie_path',
                                'new_file_permissions',
                                'new_folder_permissions'
                            );
                            if (in_array($vehicle['native_key'], $excludes)) {
                                $keep = false;
                            }
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
            return $keep;
        });
    }

    public function postInstall() {
        /* fix settings_version */
        /** @var \modSystemSetting $object */
        $object = $this->xpdo->getObject('modSystemSetting', array('key' => 'settings_version'));
        if (!$object) {
            $object = $this->xpdo->newObject('modSystemSetting');
            $object->fromArray(array(
                'key' => 'settings_version',
                'area' => 'system',
                'namespace' => 'core',
                'xtype' => 'textfield',
            ), '', true);
        }
        $object->set('value', $this->xpdo->version['full_version']);
        $object->save(false);

        /* fix session_cookie_domain */
        $object = $this->xpdo->getObject('modSystemSetting', array('key' => 'session_cookie_domain'));
        if (!$object) {
            $object = $this->xpdo->newObject('modSystemSetting');
            $object->fromArray(array(
                'key' => 'session_cookie_domain',
                'area' => 'session',
                'namespace' => 'core',
                'xtype' => 'textfield',
            ), '', true);
        }
        $object->set('value', '');
        $object->save(false);

        /* fix session_cookie_path */
        $object = $this->xpdo->getObject('modSystemSetting', array('key' => 'session_cookie_path'));
        if (!$object) {
            $object = $this->xpdo->newObject('modSystemSetting');
            $object->fromArray(array(
                'key' => 'session_cookie_path',
                'area' => 'session',
                'namespace' => 'core',
                'xtype' => 'textfield',
            ), '', true);
        }
        $object->set('value', $this->xpdo->getOption('base_url', null, MODX_BASE_URL));
        $object->save(false);
    }
} 
