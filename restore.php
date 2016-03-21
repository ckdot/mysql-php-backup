<?php
include 'bootstrap.php';

if (!isset($configuration['file'])) {
    die('Please specify a backup file to restore via --file argument.');
}

$database        = $configuration['database'];
$dateTime        = date('Y_m_d_H_i_s');
$backupDatabase  = sprintf('backup_%s_%s', $dateTime, $database);
$databasePath    = rtrim($configuration['path'], '/') . '/' . $configuration['database'];
$currentFilePath = tempnam(sys_get_temp_dir(), $configuration['database']);
$backupFile      = explode('.', $configuration['file'])[0];
$backupFilePath  = $databasePath . '/' . $backupFile . '.sql.gz';

if (!is_readable($backupFilePath)) {
    die('Backup file ' . $backupFilePath . ' not found.');
}

/*
 * Rename current database
 */
$result = createBackup($configuration['user'], $configuration['pass'], $configuration['database'], $currentFilePath);

createDatabase($configuration['user'], $configuration['pass'], $backupDatabase);
importDatabase($configuration['user'], $configuration['pass'], $backupDatabase, $currentFilePath);
dropDatabase($configuration['user'], $configuration['pass'], $database);

echo 'Current database renamed to ' . $backupDatabase . '.' . PHP_EOL;

createDatabase($configuration['user'], $configuration['pass'], $database);
importDatabase($configuration['user'], $configuration['pass'], $database, $backupFilePath);

echo 'Backup restored.' . PHP_EOL;



