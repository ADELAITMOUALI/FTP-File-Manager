<?php

function ftpConnect($username, $password) {
    $ftp_server = "127.0.0.1";
    $ftp_port = 21;

    $conn_id = ftp_connect($ftp_server, $ftp_port);
    if ($conn_id === false) {
        error_log("FTP Connect failed: " . error_get_last()['message']);
        return false;
    }

    $login_result = ftp_login($conn_id, $username, $password);
    if ($login_result === false) {
        error_log("FTP Login failed for user $username: " . error_get_last()['message']);
        ftp_close($conn_id);
        return false;
    }

    ftp_pasv($conn_id, true);
    return $conn_id;
}

function ftpDisconnect($conn_id) {
    if ($conn_id) {
        ftp_close($conn_id);
    }
}

function ftpListFiles($conn_id, $dir = '.') {
    $raw_list = ftp_rawlist($conn_id, $dir);
    if ($raw_list === false) {
        error_log('FTP RAWLIST failed: ' . error_get_last()['message']);
        return ['error' => 'Failed to list directory contents'];
    }

    $fileList = [];
    foreach ($raw_list as $item) {
        $info = preg_split("/\s+/", $item, 9);
        if (count($info) < 9) continue;

        $name = $info[8];
        $type = $info[0][0] === 'd' ? 'dir' : 'file';
        $size = $info[4];
        $date = $info[5] . ' ' . $info[6] . ' ' . $info[7];

        if ($name != '.' && $name != '..') {
            $fileList[] = [
                'name' => $name,
                'type' => $type,
                'size' => formatFileSize($size),
                'modified' => date('Y-m-d H:i:s', strtotime($date))
            ];
        }
    }

    return [
        'currentDir' => ftp_pwd($conn_id),
        'files' => $fileList
    ];
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}


function ftpUploadFile($conn_id, $local_file, $remote_file) {
    return ftp_put($conn_id, $remote_file, $local_file, FTP_BINARY);
}

function ftpDownloadFile($conn_id, $remote_file, $local_file) {
    return ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY);
}

function ftpDeleteFile($conn_id, $remote_file) {
    return ftp_delete($conn_id, $remote_file);
}

function ftpRemoveDirectory($conn_id, $directory) {
    // Get the list of files and directories
    $files = ftp_nlist($conn_id, $directory);

    if ($files === false) {
        error_log("Failed to list directory contents: $directory");
        return false;
    }

    // First, remove all files and subdirectories
    foreach ($files as $file) {
        // Exclude the '.' and '..' entries
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $directory . '/' . basename($file); // Full path to the file or directory

        // Try to change directory, if successful, it's a directory
        if (@ftp_chdir($conn_id, $filePath)) {
            // It's a directory, go back to the parent and recursively delete the subdirectory
            ftp_chdir($conn_id, ".."); // Go back to the parent directory
            if (!ftpRemoveDirectory($conn_id, $filePath)) {
                error_log("Failed to remove subdirectory: $filePath");
                return false;
            }
        } else {
            // It's a file, so delete it
            if (!ftp_delete($conn_id, $filePath)) {
                error_log("Failed to delete file: $filePath");
                return false;
            }
        }
    }

    // Finally, remove the now-empty directory
    if (!ftp_rmdir($conn_id, $directory)) {
        error_log("Failed to remove directory: $directory");
        return false;
    }

    return true;
}



function ftpCreateFolder($conn_id, $folderName) {
    return ftp_mkdir($conn_id, $folderName);
}

function ftpChangeDirectory($conn_id, $directory) {
    return ftp_chdir($conn_id, $directory);
}

function ftpRenameFile($conn_id, $oldname, $newname) {
    return ftp_rename($conn_id, $oldname, $newname);
}

function ftpGetFileSize($conn_id, $remote_file) {
    return ftp_size($conn_id, $remote_file);
}

function ftpGetFileModificationTime($conn_id, $remote_file) {
    return ftp_mdtm($conn_id, $remote_file);
}

function ftpSetFilePermissions($conn_id, $remote_file, $mode) {
    return ftp_chmod($conn_id, $mode, $remote_file);
}

function ftpGetCurrentDirectory($conn_id) {
    return ftp_pwd($conn_id);
}

function ftpListDirectoryDetails($conn_id, $directory = '.') {
    return ftp_rawlist($conn_id, $directory);
}

?>