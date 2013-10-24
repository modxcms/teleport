<?php
/**
 * @var \Teleport\Transport\TeleportTransport $transport
 * @var \modTransportPackage $object
 * @var array $options
 */
$result = true;
if ($object instanceof modTransportPackage) {
    $result = $object->install();
}
return $result;
