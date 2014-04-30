<?php
/*
 * Copyright 2014 by Francesco PIRANEO G. (fpiraneo@gmail.com)
 * 
 * This file is part of ocduplicates.
 * 
 * ocduplicates is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ocduplicates is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ocduplicates.  If not, see <http://www.gnu.org/licenses/>.
 */

\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('ocduplicates');

$user = \OCP\User::getUser();

$filesList = \OCA\OCDuplicates\utilities::huntDuplicates($user);

// Apre file
$userView = new \OC\Files\View('/' . $user . '/files');
$handle = $userView->fopen('/duplicates.txt', 'w');

if($handle === FALSE) {
    echo('1');
    die();
}

fprintf($handle, "Here are the duplicates\n");

// Loop through the results
foreach($filesList as $item) {
    $fileInfo = \OCA\OCDuplicates\utilities::getFileInfo($item);
    fprintf($handle, "%s, %s\n", $fileInfo['path'], $fileInfo['name']);
}

// Chiude
fclose($handle);

echo('0');