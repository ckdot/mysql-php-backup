<?php
include 'bootstrap.php';

$dateTime     = date(DateTime::W3C);
$databasePath = rtrim($configuration['path'], '/') . '/' . $configuration['database'];
$fileName     = $dateTime . '.sql';
$fullPath     = $databasePath . '/' . $fileName;
$result       = createBackup($configuration['user'], $configuration['pass'], $configuration['database'], $fullPath);

echo 'Backup generated: ' . $fullPath . PHP_EOL;

compressBackup($fullPath);

echo 'Backup zipped.' . PHP_EOL;

$handle        = opendir($databasePath);
$rotationLimit = time() - $configuration['rotationDays'] * 24 *60 * 60;

while ($file = readdir($handle)) {
	if ('.' === $file{0}) {
		continue;
	}

	$time      = explode('.', $file)[0];
	$timestamp = strtotime($time);

	if ($timestamp < $rotationLimit) {
		$path = $databasePath . '/' . $file;
		unlink($path);

		echo $path . ' deleted.' . PHP_EOL;
	}

}

echo '...done!' . PHP_EOL;