<?php
/**
 * @param string[] $argv
 * @return string[]
 */
function getConfiguration(array $argv)
{
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

    return $configuration;
}

/**
 * @param string $user
 * @param string $pass
 * @param string $database
 * @param string $path
 * @return int
 */
function createBackup($user, $pass, $database, $path)
{
    $dir = dirname($path);

    if (!is_dir($dir)) {
        mkdir($dir);
    }

    $command = sprintf(
        'mysqldump --user=%s --password=%s %s > %s',
        $user, $pass, $database, $path
    );

    exec($command, $output, $result);

    if (MYSQLDUMP_EXIT_SUCCESS !== $result) {
        echo 'MySQL Dump error on: ' . $command;
        die;
    }

    return $result;
}

/**
 * @param string $path
 * @return int
 */
function compressBackup($path)
{
    exec('gzip ' . $path, $output, $result);

    return $result;
}

/**
 * @param string $user
 * @param string $pass
 * @param string $sql
 */
function query($user, $pass, $sql)
{
    $command = sprintf("mysql -u %s -p%s  -e '%s'", $user, $pass, $sql);
    exec($command, $output, $result);

    if (MYSQL_EXIT_SUCCESS !== $result) {
        echo 'MySQL error on ' . $command;
        die;
    }

    return $result;
}

/**
 * @param string $user
 * @param string $pass
 * @param string $database
 * @return int
 */
function createDatabase($user, $pass, $database)
{
    $sql = 'CREATE DATABASE ' . $database;
    return query($user, $pass, $sql);
}

/**
 * @param string $user
 * @param string $pass
 * @param string $database
 * @param string $path
 * @return int
 */
function importDatabase($user, $pass, $database, $path)
{
    $pathInfo = pathinfo($path);
    $command  = sprintf('mysql -u %s -p%s %s < %s', $user, $pass, $database, $path);

    if (isset($pathInfo['extension']) && 'gz' === $pathInfo['extension']) {
        $command  = sprintf('gunzip < %s | mysql -u %s -p%s %s', $path, $user, $pass, $database);
    }

    exec($command, $output, $result);

    if (MYSQL_EXIT_SUCCESS !== $result) {
        echo 'MySQL error on ' . $command;
        die;
    }

    return $result;
}

/**
 * @param string $user
 * @param string $pass
 * @param string $database
 */
function dropDatabase($user, $pass, $database)
{
    $sql = 'DROP DATABASE ' . $database;
    return query($user, $pass, $sql);
}