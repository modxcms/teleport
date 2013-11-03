<?php
/**
 * @var \Teleport\Transport\Transport $transport
 * @var \modTransportPackage $object
 * @var array $options
 */
$result = true;
if ($object instanceof modTransportPackage) {
    $result = $object->install();
}
return $result;
