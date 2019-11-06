<?php
/**
 * @var \Teleport\Transport\Transport $transport
 * @var \MODX\Revolution\Transport\modTransportPackage $object
 * @var array $options
 */

$result = true;
if ($object instanceof \MODX\Revolution\Transport\modTransportPackage) {
    $result = $object->install();
}
return $result;
