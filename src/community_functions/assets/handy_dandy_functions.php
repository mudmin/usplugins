<?php

// Checks if the user is logged in and dumps the data if they are
if (!function_exists("comLiDump")) {
  function comLiDump($data) {
    global $user;
    if (isset($user) && $user->isLoggedIn()) {
      dump($data);
    }
  }
}

// Formats byte values into readable units
if (!function_exists("comFormatSizeUnits")) {
  function comFormatSizeUnits($bytes) {
    $bytes = (int)$bytes ?? 0;
    if ($bytes >= 1073741824) {
      $bytes = number_format($bytes / 1073741824, 1) . ' GB';
    } elseif ($bytes >= 1048576) {
      $bytes = number_format($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
      $bytes = number_format($bytes / 1024, 1) . ' KB';
    } elseif ($bytes > 1) {
      $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
      $bytes = $bytes . ' byte';
    } else {
      $bytes = '0 bytes';
    }
    return $bytes;
  }
}

// Deletes a directory tree
if (!function_exists("comDelTree")) {
  function comDelTree($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? comDelTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }
}

// Formats a date string
if (!function_exists("comFormatDate")) {
  function comFormatDate($date, $fullDay = false) {
    if ($date == null || $date == "") {
      return "";
    }
    if ($fullDay == false) {
      return date("D M d, Y", strtotime($date));
    } else {
      return date("l, F j, Y", strtotime($date));
    }
  }
}

// Formats a date string for the US date format
if (!function_exists("comFormatUsaDate")) {
  function comFormatUsaDate($datetimeString) {
    $dateTime = new DateTime($datetimeString);
    $formattedDate = $dateTime->format('n/j/Y');
    return ltrim($formattedDate, '0');
  }
}

// Formats a time string
if (!function_exists("comFormatTime")) {
  function comFormatTime($time, $seconds = false) {
    if ($time == null || $time == "") {
      return "";
    }
    if ($seconds == false) {
      return date("g:i a", strtotime($time));
    } else {
      return date("g:i:s A", strtotime($time));
    }
  }
}

// Formats a datetime string
if (!function_exists("comFormatDt")) {
  function comFormatDt($datetime) {
    if ($datetime == null || $datetime == "") {
      return "";
    }
    return date("D M d, Y - g:i a", strtotime($datetime));
  }
}

// Formats elapsed time
if (!function_exists("comFormatEt")) {
  function comFormatEt($time, $neverSeconds = false) {
    if ($time == "") {
      return "";
    }
    $timeParts = explode(':', $time);
    if (count($timeParts) < 2) {
      return "";
    }
    $hours = intval($timeParts[0]);
    $minutes = intval($timeParts[1]);
    $seconds = intval($timeParts[2]);

    if ($hours > 0) {
      // Format as hours and minutes
      $formattedTime = "{$hours} hours, {$minutes} minutes";
    } else {
      // Format as minutes and seconds
      if ($minutes == 0 && $seconds > 0) {
        $formattedTime = "{$seconds} seconds";
      } else {
        $formattedTime = "{$minutes} minutes";
        if ($neverSeconds == false) {
          $formattedTime .= ", {$seconds} seconds";
        }
      }
    }

    return $formattedTime;
  }
}

// Converts seconds into a formatted time string
if (!function_exists("comIntToTime")) {
  function comIntToTime($seconds) {
    $seconds = (int) $seconds;
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
  }
}

// Converts a camelCase string into a space-separated string
if (!function_exists("comParseCamelCase")) {
  function comParseCamelCase($string) {
    $string = str_replace("_", "", $string);
    $string = preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
    return ucwords($string);
  }
}

// Compresses an image to a specified quality and saves it to a destination
if (!function_exists("comCompressImage")) {
  function comCompressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg')
      $image = imagecreatefromjpeg($source);
    elseif ($info['mime'] == 'image/gif')
      $image = imagecreatefromgif($source);
    elseif ($info['mime'] == 'image/png')
      $image = imagecreatefrompng($source);
    imagejpeg($image, $destination, $quality);
    return $destination;
  }
}

// Converts a numeric string to a formatted version string with three components
if (!function_exists("comNumberToVersion")) {
  function comNumberToVersion($ver) {
    $ver = str_split($ver); // Split the number into individual digits
    $length = count($ver);

    // Ensure that the version has at least three parts
    while ($length < 3) {
      array_unshift($ver, '0'); // Prepend '0' if there are fewer than 3 digits
      $length++;
    }

    // Construct the version string from parts
    $version = implode('.', array_slice($ver, 0, $length - 2)) // Join all but the last two digits with dots
              . '.' . $ver[$length - 2] // Second last digit
              . '.' . $ver[$length - 1]; // Last digit

    return $version;
  }
}

