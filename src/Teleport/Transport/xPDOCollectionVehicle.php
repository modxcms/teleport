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

/**
 * A custom \xPDOVehicle implementation for installing a collection of xPDOObjects.
 *
 * @package Teleport\Transport
 */
class xPDOCollectionVehicle extends \xPDOObjectVehicle {
    public $class = 'xPDOCollectionVehicle';

    /**
     * Put a representation of an xPDOObject collection into this vehicle.
     *
     * @param \xPDOTransport $transport The transport package hosting the vehicle.
     * @param mixed &$object A reference to the artifact this vehicle will represent.
     * @param array $attributes Additional attributes represented in the vehicle.
     */
    public function put(& $transport, & $object, $attributes = array ()) {
        $this->payload['guid'] = md5(uniqid(rand(), true));
        if (is_array($object)) {
            if (!isset($this->payload['object']) || !is_array($this->payload['object'])) {
                $this->payload['object'] = array();
            }
            $obj = reset($object);
            if (!isset ($this->payload['package'])) {
                if ($obj instanceof \xPDOObject) {
                    $packageName = $obj->_package;
                } else {
                    $packageName = '';
                }
                $this->payload['package'] = $packageName;
            }
            if (!isset($this->payload['class']) && $obj instanceof \xPDOObject) {
                $this->payload['class'] = $obj->_class;
            }
            while ($obj) {
                $payload = array_merge(
                    $attributes,
                    array(
                        'package' => $this->payload['package'],
                        'class' => $this->payload['class'],
                    )
                );
                if ($obj instanceof \xPDOObject) {
                    $nativeKey = $obj->getPrimaryKey();
                    $payload['object'] = $obj->toJSON('', true);
                    $payload['guid'] = md5(uniqid(rand(), true));
                    $payload['native_key'] = $nativeKey;
                    $payload['signature'] = md5($payload['class'] . '_' . $payload['guid']);
                    if (isset ($payload[\xPDOTransport::RELATED_OBJECTS]) && !empty ($payload[\xPDOTransport::RELATED_OBJECTS])) {
                        $relatedObjects = array ();
                        foreach ($obj->_relatedObjects as $rAlias => $related) {
                            if (is_array($related)) {
                                foreach ($related as $rKey => $rObj) {
                                    if (!isset ($relatedObjects[$rAlias]))
                                        $relatedObjects[$rAlias] = array ();
                                    $guid = md5(uniqid(rand(), true));
                                    $relatedObjects[$rAlias][$guid] = array ();
                                    $this->_putRelated($transport, $rAlias, $rObj, $relatedObjects[$rAlias][$guid]);
                                }
                            }
                            elseif (is_object($related)) {
                                if (!isset ($relatedObjects[$rAlias]))
                                    $relatedObjects[$rAlias] = array ();
                                $guid = md5(uniqid(rand(), true));
                                $relatedObjects[$rAlias][$guid] = array ();
                                $this->_putRelated($transport, $rAlias, $related, $relatedObjects[$rAlias][$guid]);
                            }
                        }
                        if (!empty ($relatedObjects))
                            $payload['related_objects'] = $relatedObjects;
                    }
                }
                elseif (is_object($obj)) {
                    $payload['object'] = $transport->xpdo->toJSON(get_object_vars($obj));
                    $payload['native_key'] = $payload['guid'];
                    $payload['signature'] = md5($payload['class'] . '_' . $payload['guid']);
                }
                $this->payload['object'][] = $payload;
                $obj = next($object);
            }
        }
        parent :: put($transport, $object, $attributes);
    }

    /**
     * Install the vehicle artifact into a transport host.
     *
     * @param \xPDOTransport &$transport A reference to the transport.
     * @param array $options An array of options for altering the installation of the artifact.
     * @return boolean True if the installation of the vehicle artifact was successful.
     */
    public function install(& $transport, $options) {
        $installed = false;
        if (is_array($this->payload['object'])) {
            $installed = 0;
            foreach ($this->payload['object'] as $payload) {
                $parentObj = null;
                $parentMeta = null;
                if ($this->_installObject($transport, $options, $payload, $parentObj, $parentMeta)) {
                    $installed++;
                }
            }
            $installed = $installed == count($this->payload['object']) ? true : false;
        }
        return $installed;
    }

    /**
     * This vehicle implementation does not yet support uninstall.
     *
     * @param \xPDOTransport &$transport A reference to the transport.
     * @param array $options An array of options for altering the uninstallation of the artifact.
     * @return boolean True, always.
     */
    public function uninstall(& $transport, $options) {
        return true;
    }
}
