<?php

/**
 * SMART loader object test suite.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/06/2016
 */

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");

//
// Include test class.
//
require_once( kPATH_LIBRARY_ROOT . "src/StataFile.php" );

//
// Instantiate object.
//
echo( '$test = new StataFile();' . "\n" );
$test = new StataFile();
print_r( $test );

echo( "\n====================================================================================\n\n" );

//
// Read file.
//
echo( '$result = $test->Read( kPATH_LIBRARY_ROOT . "test/Read1.dta" );' . "\n" );
$result = $test->Read( kPATH_LIBRARY_ROOT . "test/Read1.dta" );
var_dump( $result->getRealPath() );
print_r( $test );

echo( "\n====================================================================================\n\n" );

//
// Instantiate object.
//
echo( '$test = new StataFile();' . "\n" );
$test = new StataFile();
echo( '$test->Format( "118" );' . "\n" );
$test->Format( "118" );
echo( '$test->ByteOrder( "LSF" );' . "\n" );
$test->ByteOrder( "LSF" );
echo( '$test->VariablesCount( 5 );' . "\n" );
$test->VariablesCount( 5 );
echo( '$test->ObservationsCount( 3 );' . "\n" );
$test->ObservationsCount( 3 );
echo( '$test->DatasetLabel( "Test dataset" );' . "\n" );
$test->DatasetLabel( "Test dataset" );
echo( '$test->VariableType( NULL, [ "int", "double", "str9", "int", "strL" ], TRUE );' . "\n" );
$test->VariableType( NULL, [ "int", "double", "str9", "int", "strL" ], TRUE );
echo( '$test->VariableName( NULL, [ "INTEGER", "FLOAT", "STRING", "CATEGORY", "LONG_STRING" ] );' . "\n" );
$test->VariableName( NULL, [ "INTEGER", "FLOAT", "STRING", "CATEGORY", "LONG_STRING" ] );
echo( '$test->VariableSort( NULL, [ "CATEGORY" => 1, "INTEGER" => 2, "FLOAT" => 3 ], TRUE );' . "\n" );
$test->VariableSort( NULL, [ "CATEGORY" => 1, "INTEGER" => 2, "FLOAT" => 3 ], TRUE );
print_r( $test );

echo( "\n" );

//
// Write file.
//
echo( '$result = $test->Write( kPATH_LIBRARY_ROOT . "test/Write1.dta" );' . "\n" );
$result = $test->Write( kPATH_LIBRARY_ROOT . "test/Write1.dta" );
var_dump( $result->getRealPath() );
print_r( $test );


?>

