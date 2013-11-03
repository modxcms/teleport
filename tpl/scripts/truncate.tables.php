<?php
/** @var \Teleport\Transport\Transport $transport  */
$results = array();
if (isset($object['classes'])) {
    foreach ($object['classes'] as $class) {
        $results[$class] = $transport->xpdo->exec('TRUNCATE TABLE ' . $transport->xpdo->getTableName($class));
    }
}
$transport->xpdo->log(xPDO::LOG_LEVEL_INFO, "Table truncation results: " . print_r($results, true));
return !array_search(false, $results, true);
