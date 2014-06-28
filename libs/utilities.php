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

namespace OCA\OCDuplicates;
class utilities {    
    /**
    * Get all files ID of the indicated user
    * @param string $user Username
    * @param string $path Path to get the content
    * @param boolean $onlyID Get only the ID of files
    * @param boolean $indexed Output result as dictionary array with fileID as index
    * @return array ID of all the files
    */
    public static function getFileList($user, $path = '', $onlyID = FALSE, $indexed = FALSE) {
        $result = array();

        $dirView = new \OC\Files\View('/' . $user);
        $dirContent = $dirView->getDirectoryContent($path);
        
        foreach($dirContent as $item) {
            $itemRes = array();
            
            if(strpos($item['mimetype'], 'directory') === FALSE) {
                $fileData = array('fileid'=>$item['fileid'], 'name'=>$item['name'], 'mimetype'=>$item['mimetype']);
                $fileData['path'] = isset($item['usersPath']) ? $item['usersPath'] : $item['path'];
                        
                $itemRes[] = ($onlyID) ? $item['fileid'] : $fileData;
            } else {
                // Case by case build appropriate path
                if(isset($item['usersPath'])) {
                    // - this condition when usersPath is set - i.e. Shared files
                    $itemPath = $item['usersPath'];
                } elseif(isset($item['path'])) {
                    // - Standard case - Normal user's folder
                    $itemPath = $item['path'];
                } else {
                    // - Special folders - i.e. sharings
                    $itemPath = 'files/' . $item['name'];
                }

                $itemRes = \OCA\OCDuplicates\utilities::getFileList($user, $itemPath, $onlyID);
            }            
            
            foreach($itemRes as $item) {
                if($onlyID) {
                    $result[] = intval($item);
                } else {
                    if($indexed) {
                        $result[intval($item['fileid'])] = $item;
                    } else {
                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns file informations
     * @param Integer $id file's ID
     */
    public static function getFileInfo($id) {
        $sql = 'SELECT storage, path, name FROM *PREFIX*filecache WHERE fileid=?';
        $args = array($id);
        $query = \OCP\DB::prepare($sql);
        $resRsrc = $query->execute($args);

        while($row = $resRsrc->fetchRow()) {
            $result = array('storage' => $row['storage'], 'path' => $row['path'], 'name' => $row['name']);
        }
        
        return $result;
    }
        
    /**
     * Add item to storage - Compute SHA1 and store on DB
     * @param array $params All parameters passed by hook
     */
    public static function addItem($params) {
        // Get file's ID
        $path = $params['path'];
        $fileInfos = \OC\Files\Filesystem::getFileInfo($path);
        $mime = $fileInfos['mimetype'];

        // Execute only if not a directory
        if(strpos($mime, 'directory') === FALSE) {
            \OCA\OCDuplicates\utilities::setFileSignature($path);
        }
    }
    
    /**
     * Set the signature for a given file path
     * @param string $filePath
     */
    public static function setFileSignature($filePath) {
        // Get local file's path
        $user = \OCP\User::getUser();

        $userView = new \OC\Files\View('/' . $user . '/files');

        // Get file's ID
        $fileInfos = \OC\Files\Filesystem::getFileInfo($filePath);
        $fileID = $fileInfos['fileid'];
        
        $handle = $userView->fopen($filePath, 'r');

        $ctx = hash_init('sha1');
        hash_update_stream($ctx, $handle);
        $fileHash = hash_final($ctx);

        // Remove old record (if one)
        $sql = 'DELETE FROM *PREFIX*ocduplicates_signatures WHERE fileid=?';
        $args = array($fileID);
        $query = \OCP\DB::prepare($sql);
        $query->execute($args);

        // Insert new record
        $sql = 'INSERT INTO *PREFIX*ocduplicates_signatures (fileid, signature) VALUES (?,?)';
        $args = array($fileID, $fileHash);
        $query = \OCP\DB::prepare($sql);
        $query->execute($args);
    }

    /**
    * Remove item from storage - Remove SHA1 from DB
    * @param array $params All parameters passed by hook
    */
    public static function removeItem($params) {
        // Get file's ID
        $path = $params['path'];
        $fileInfos = \OC\Files\Filesystem::getFileInfo($path);
        $fileID = $fileInfos['fileid'];

        // Remove old record (if one)
        if(strpos($fileInfos['mimetype'], 'directory') === FALSE) {
            \OCA\OCDuplicates\utilities::removeFile($fileID);
        } else {
            \OCA\OCDuplicates\utilities::removeDir($fileID);
        }
    }
    
    /**
     * Remove the signature of a file from DB
     * @param type $fileID
     */
    private static function removeFile($fileID) {
        $sql = 'DELETE FROM *PREFIX*ocduplicates_signatures WHERE fileid=?';
        $args = array($fileID);
        $query = \OCP\DB::prepare($sql);
        $query->execute($args);        
    }
    
    /**
     * Remove the signatures of the content of a directory from the DB
     * @param type $dirID
     */
    private static function removeDir($dirID) {
        // Get path for dirID
        $dirInfo = \OCA\OCDuplicates\utilities::getFileInfo($dirID);
        
        // Get view for actual dir
        $user = \OCP\User::getUser();
        $dirView = new \OC\Files\View('/' . $user . $dirInfo['path']);
        $dirContent = $dirView->getDirectoryContent($dirInfo['path']);
        
        // Loop through the directory content to properly delete each item
        foreach ($dirContent as $item) {
            if(strpos($item['mimetype'], 'directory') === FALSE) {
                \OCA\OCDuplicates\utilities::removeFile($item['fileid']);
            } else {
                \OCA\OCDuplicates\utilities::removeDir($item['fileid']);
            }            
        }
    }
    
    /**
     * Get files ids without signatures
     * @param String $user Username
     * @return array File's without signature IDs
     */
    public static function getIDsWithoutSignatures($user) {
        // All user's file
        $files = utilities::getFileList($user, 'files/', TRUE);
        
        // Files with signature
        $withoutSignature = array();
        
        foreach($files as $idtosearch) {
            $sql = 'SELECT fileid FROM *PREFIX*ocduplicates_signatures WHERE fileid=?';
            $args = array($idtosearch);
            $query = \OCP\DB::prepare($sql);
            $resRsrc = $query->execute($args);

            $result = FALSE;
            while($row = $resRsrc->fetchRow()) {
               $result = TRUE;
            }
            
            if(!$result) {
                $withoutSignature[] = $idtosearch;
            }
        }
        
        return $withoutSignature;
    }
    
    /**
     * Get all files ID belonging to given user
     * @param String $user Username
     * @return array All file's IDs
     */
    public static function getAllDocs($user) {
        // All user's file
        $allDocs = utilities::getFileList($user, 'files/', TRUE);
                
        return $allDocs;
    }

    /**
     * Hunt duplicates for supplied user
     * @param string $user Username
     */
    public static function huntDuplicates($user) {
        $sql = 'SELECT distinct a.fileid, a.signature ' . 
                'FROM *PREFIX*ocduplicates_signatures a ' .
                'INNER JOIN *PREFIX*ocduplicates_signatures b ON a.signature = b.signature ' .
                'WHERE a.fileid <> b.fileid';
        $args = array();
        $query = \OCP\DB::prepare($sql);
        $resRsrc = $query->execute($args);

        $duplicates = array();
        while($row = $resRsrc->fetchRow()) {
           $duplicates[] = $row['fileid'];
        }
        
        $userfile = utilities::getFileList($user, 'files/', TRUE);
        
        $userDuplicates = array_intersect($userfile, $duplicates);
        
        return $userDuplicates;
    }
}
