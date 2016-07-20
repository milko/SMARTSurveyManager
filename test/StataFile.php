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
echo( '$test->VariableFormat( NULL, [ "INTEGER" => "%10.0g", "FLOAT" => "%10.0g", "STRING" => "%9s", "CATEGORY" => "%8.0g", "LONG_STRING" => "%-80s" ], TRUE );' . "\n" );
$test->VariableFormat( NULL, [ "INTEGER" => "%10.0g", "FLOAT" => "%10.0g", "STRING" => "%9s", "CATEGORY" => "%8.0g", "LONG_STRING" => "%-80s" ], TRUE );
echo( '$test->VariableLabel( NULL, [ "CATEGORY" => "Colors" ], TRUE );' . "\n" );
$test->VariableEnumName( NULL, [ "CATEGORY" => "Colors" ], TRUE );
echo( '$test->VariableLabel( NULL, [ "INTEGER" => "This is an integer", "FLOAT" => "This is a float", "STRING" => "This is a string", "CATEGORY" => "This is a controlled vocabulary", "LONG_STRING" => "This is a long string" ], TRUE );' . "\n" );
$test->VariableLabel( NULL, [ "INTEGER" => "This is an integer", "FLOAT" => "This is a float", "STRING" => "This is a string", "CATEGORY" => "This is a controlled vocabulary", "LONG_STRING" => "This is a long string" ], TRUE );
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

