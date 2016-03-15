<?php
/* CONFIGURATION */
$user         = 'root';
$pass         = 'jukehaus14';
$database     = 'lurchdb';
$path         = '/backup';
$rotationDays =  2;

/* IMPLEMENTATION */
$dateTime     = date(DateTime::W3C);
$databasePath = rtrim($path, '/') . '/' . $database;
$fileName     = $dateTime . '.sql';
$fullPath     = $databasePath . '/' . $fileName;

$command      = sprintf(
    'mysqldump --user=%s --password=%s %s > %s 2>/dev/null',
    $user, $pass, $database, $fullPath
);

if (!is_dir($databasePath)) {
	mkdir($databasePath);
}

exec($command);

echo 'Backup generated: ' . $fullPath . PHP_EOL;

exec('gzip ' . $fullPath);

echo 'Backup zipped.' . PHP_EOL;

$handle          = opendir($databasePath);
$rotationLimit   = time() - $rotationDays * 24 *60 * 60;

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