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

OCP\User::checkLoggedIn();

// Handle translations
$l = new \OC_L10N('ocduplicates');

\OCP\Util::addscript('ocduplicates', 'huntduplicates');

$tmpl = new \OCP\Template('ocduplicates', 'personal-settings');

$user = \OCP\User::getUser();
$tmpl->assign('withoutSignature', count(OCA\OCDuplicates\utilities::getIDsWithoutSignatures($user)));
$tmpl->assign('allDocs', count(OCA\OCDuplicates\utilities::getAllDocs($user)));

$duplicates = count(OCA\OCDuplicates\utilities::huntDuplicates($user));
$tmpl->assign('duplicates', $duplicates);

return $tmpl->fetchPage();