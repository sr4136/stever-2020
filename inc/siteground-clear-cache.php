<?php

/**
 * Finds the last modified time of files in a directory and its subdirectories.
 *
 * @param string $dir The directory to search in.
 * @return int The last modified time of the most recently modified file.
 */
function find_last_modified_filetime($dir) {
	$lastModifiedTime = 0; // Initialize to 0 to compare with file modification times
	$root = scandir($dir); // Get the list of files and directories in the specified directory

	foreach ($root as $value) {
		// Skip the current and parent directory entries
		if ($value === '.' || $value === '..') {
			continue;
		}

		// Construct the full path of the file or directory
		$fullPath = "$dir/$value";
		$fullPath = preg_replace('/\?v=[\d]+$/', '', $fullPath);

		// Check if the current item is a file
		if (file_exists($fullPath) &&is_file($fullPath)) {
			$fileMtime = filemtime($fullPath); // Get the last modified time of the file
			// Update lastModifiedTime if the current file is more recent
			if ($fileMtime > $lastModifiedTime) {
				$lastModifiedTime = $fileMtime;
			}
			continue; // Move to the next item in the directory
		}

		// If it's a directory, recursively search for modified files within it
		$subDirLastModifiedFile = find_last_modified_filetime($fullPath);
		if ($subDirLastModifiedFile) {
			if (file_exists($subDirLastModifiedFile) && is_file($subDirLastModifiedFile)) {
				$subDirMtime = filemtime($subDirLastModifiedFile); // Get the last modified time of the found file
				// Update lastModifiedTime if the found file is more recent
				if ($subDirMtime > $lastModifiedTime) {
					$lastModifiedTime = $subDirMtime;
				}
			}
		}
	}

	return $lastModifiedTime; // Return the last modified time found
}

/**
 * Compare last known change (in transient) to current  and clears the SG cache if they have.
 */
function check_theme_files_change() {

	// Only run when logged in!
	if (is_user_logged_in()) {
		$target_dir = wp_get_theme()->get_stylesheet_directory(); // Get the theme's stylesheet directory
		$check_for_newest = find_last_modified_filetime($target_dir); // Find the last modified time of theme files
		$theme_last_modified = get_transient('sr_theme_last_modified'); // Retrieve the stored last modified time

		// Check if the transient is false or if the new last modified time is greater than the stored one
		if ($theme_last_modified === false || $check_for_newest > $theme_last_modified) {
			// echo '<script type="text/javascript">alert("Purged");</script>';
			// Clear the cache if files have changed
			if (function_exists('sg_cachepress_purge_cache')) {
				sg_cachepress_purge_cache(); // Purge the cache using the specific function
			}
			// Update the transient with the new last modified time
			set_transient('sr_theme_last_modified', $check_for_newest, HOUR_IN_SECONDS); // Store the new last modified time
		}
	}
}

// Hook the check_theme_files_change function to the 'init' action
add_action('init', 'check_theme_files_change');
