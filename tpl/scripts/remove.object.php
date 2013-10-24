<?php
/**
 * @var \Teleport\Transport\TeleportTransport $transport
 * @var array $object
 */
$result = false;
if (isset($object['class']) && isset($object['pk'])) {
    $instance = $transport->xpdo->getObject($object['class'], $object['pk']);
    $result = $instance ? $instance->remove() : true;
}
return $result;
