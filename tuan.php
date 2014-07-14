<?php
namespace www\week2\day1\dinhtuan;

ini_set('memory_limit','256M');

// $startTime = microtime(true);


// Init & Register Autoloader
require "SplClassLoader.php";
$loader = new \SplClassLoader('www', 'D:\wamp');
$loader->register();

define('FILE_NAME', IMPORT_FILE_NAME);
define('DB_NAME', 'rubikin_db');
define('DB_USER', 'root');
define('DB_PASS', '');


$db = new \mysqli('localhost', DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
    die("Cannot connect to the database. Error: " . mysqli_connect_error());
}


//////////////////////////////////////
// The default mapping is like this.
// If you wish to change the mapping,
// then modify the array below and
// uncomment the setmapping command
//////////////////////////////////////

/*$mappingArray = array(
        'prod_id' => 0,
        'prod_name' => 1,
        'prod_slug' => 2,
        'prod_short' =>3,
        'prod_descr' => 4,
        'prod_available' => 5,
        'prod_create' => 6,
        'prod_update' => 7,
        'prod_delete' => 8,
        'prod_method' => 9,
        'category' => 12
    );*/

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// USE CLASS ImportBySQL //////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////


// $chay = new ImportBySQL(FILE_NAME, $db);

// // Use for remapping
// $chay->setMapping($mappingArray);

// $chay->running();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////// USE CLASS ImportImprove ///////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$chay = new ImportImprove(FILE_NAME);

// // Use for remapping
// $chay->setMapping($mappingArray);

$chay->generateCSVs();

$chay->import($db);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$db->close();

// $endTime = microtime(true);
// $duration = $endTime - $startTime;
// echo ("Time: " . $duration);
