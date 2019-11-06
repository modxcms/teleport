<?php
/**
 * @var \Teleport\Transport\Transport $transport
 * @var array $object
 */
$result = false;
if (isset($object['class']) && isset($object['criteria'])) {
    $query = $transport->xpdo->newQuery($object['class']);
    $query->setClassAlias($object['criteria']['alias']);
    $query->query = $object['criteria']['query'];
    if ($query->prepare()) {
        $affected = $query->stmt->execute();
        if ($affected === false) {
            $transport->xpdo->log(\xPDO\xPDO::LOG_LEVEL_ERROR, "Could not execute PDOStatement from xPDOQuery: " . print_r($query->stmt->errorInfo(), true));
        }
    } else {
        $transport->xpdo->log(\xPDO\xPDO::LOG_LEVEL_ERROR, "Could not prepare PDOStatement from xPDOQuery: {$query->toSQL()}");
    }
} else {
    $transport->xpdo->log(\xPDO\xPDO::LOG_LEVEL_ERROR, "No valid class or criteria provided to extract script " . basename(__FILE__));
}
return $result;
