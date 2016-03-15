<?php
$configurationPath = __DIR__ . '/config.php';
$configuration     = [
	'user'         => null,
	'pass'         => null,
	'database'    => null,
	'path'         => null,
	'rotationDays' => 365
];

if (file_exists($configurationPath)) {
	$customConfiguration = include($configurationPath);
	$configuration = array_merge($configuration, $customConfiguration);
}

foreach ($argv as $argument) {
	if ('--' === substr($argument, 0, 2)) {
		$keyValue = explode('=', substr($argument, 2));

		if (2 !== count($keyValue)) {
			die('Arguments need to be passed like: backup.php --configurationKey=configurationValue');
		}

		$key   = $keyValue[0];
		$value = $keyValue[1];

		$configuration[$key] = $value;
	}
}

$dateTime     = date(DateTime::W3C);
$databasePath = rtrim($configuration['path'], '/') . '/' . $configuration['database'];
$fileName     = $dateTime . '.sql';
$fullPath     = $databasePath . '/' . $fileName;

$command      = sprintf(
    'mysqldump --user=%s --password=%s %s > %s',
    $configuration['user'], $configuration['pass'], $configuration['database'], $fullPath
);

if (!is_dir($databasePath)) {
	mkdir($databasePath);
}

exec($command);

echo 'Backup generated: ' . $fullPath . PHP_EOL;

exec('gzip ' . $fullPath);

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