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
// Init benchmark.
//
$bench = new Ubench();

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
echo( '$result = $test->Read( kPATH_LIBRARY_ROOT . "test/READ.dta" );' . "\n" );
$bench->start();
$result = $test->Read( kPATH_LIBRARY_ROOT . "test/LARGE.dta" );
$bench->end();
var_dump( $result->getRealPath() );
print_r( $test );
echo( "\nTime: " . $bench->getTime() . "\n" );
echo( "Memory: " . $bench->getMemoryPeak() . "\n" );

echo( "\n" );

//
// Write file.
//
echo( '$result = $test->Write( kPATH_LIBRARY_ROOT . "test/WRITE.dta" );' . "\n" );
$bench->start();
$result = $test->Write( kPATH_LIBRARY_ROOT . "test/WRITE.dta" );
$bench->end();
var_dump( $result->getRealPath() );
print_r( $test );
echo( "\nTime: " . $bench->getTime() . "\n" );
echo( "Memory: " . $bench->getMemoryPeak() . "\n\n" );
exit;

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
echo( '$test->VariablesCount( 9 );' . "\n" );
$test->VariablesCount( 9 );
echo( '$test->ObservationsCount( 4 );' . "\n" );
$test->ObservationsCount( 4 );
echo( '$test->DatasetLabel( "Test dataset" );' . "\n" );
$test->DatasetLabel( "Test dataset" );
echo( '$test->VariableType( NULL, [ "byte", "int", "long", "float", "double", "str9", "int", "int", "strL" ], TRUE );' . "\n" );
$test->VariableType( NULL, [ "byte", "int", "long", "float", "double", "str9", "int", "int", "strL" ], TRUE );
echo( '$test->VariableName( NULL, [ "BYTE", "INTEGER", "LONG", "FLOAT", "DOUBLE", "STRING", "COLOR", "PLACE", "LONG_STRING" ] );' . "\n" );
$test->VariableName( NULL, [ "BYTE", "INTEGER", "LONG", "FLOAT", "DOUBLE", "STRING", "COLOR", "PLACE", "LONG_STRING" ] );
echo( '$test->VariableSort( NULL, [ "STRING" => 1, "LONG" => 2, "PLACE" => 3 ], TRUE );' . "\n" );
$test->VariableSort( NULL, [ "STRING" => 1, "LONG" => 2, "PLACE" => 3 ], TRUE );
echo( '$test->VariableFormat( NULL, [ "BYTE" => "%8.0g", "INTEGER" => "%8.0g", "LONG" => "%12.0g", "FLOAT" => "%9.0g", "DOUBLE" => "%10.0g", "STRING" => "%9s", "COLOR" => "%8.0g", "PLACE" => "%8.0g", "LONG_STRING" => "%-40s" ], TRUE );' . "\n" );
$test->VariableFormat( NULL, [ "BYTE" => "%8.0g", "INTEGER" => "%8.0g", "LONG" => "%12.0g", "FLOAT" => "%9.0g", "DOUBLE" => "%10.0g", "STRING" => "%9s", "COLOR" => "%8.0g", "PLACE" => "%8.0g", "LONG_STRING" => "%-40s" ], TRUE );
echo( '$test->VariableEnumeration( NULL, [ "COLOR" => "Colors", "PLACE" => "Cardinal" ], TRUE );' . "\n" );
$test->VariableEnumeration( NULL, [ "COLOR" => "Colors", "PLACE" => "Cardinal" ], TRUE );
echo( '$test->VariableLabel( NULL, [ "BYTE" => "Byte", "INTEGER" => "32 bit integer", "LONG" => "64 bit integer", "FLOAT" => "32 bit float", "DOUBLE" => "64 bit float", "STRING" => "Fixed length string", "COLOR" => "Colours enumeration", "PLACE" => "Directions enumeration", "LONG_STRING" => "Long string" ], TRUE );' . "\n" );
$test->VariableLabel( NULL, [ "BYTE" => "Byte", "INTEGER" => "32 bit integer", "LONG" => "64 bit integer", "FLOAT" => "32 bit float", "DOUBLE" => "64 bit float", "STRING" => "Fixed length string", "COLOR" => "Colours enumeration", "PLACE" => "Directions enumeration", "LONG_STRING" => "Long string" ], TRUE );
echo( '$result = $test->Note( "Dataset note 1" );' . "\n" );
$result = $test->Note( "Dataset note 1" );
print_r( $result );
echo( '$result = $test->Note( "Dataset note 2" );' . "\n" );
$result = $test->Note( "Dataset note 2" );
print_r( $result );
echo( '$result = $test->Note( "Dataset note 3" );' . "\n" );
$result = $test->Note( "Dataset note 3" );
print_r( $result );
echo( '$result = $test->Note( "BYTE note 1", "BYTE" );' . "\n" );
$result = $test->Note( "BYTE note 1", "BYTE" );
print_r( $result );
echo( '$result = $test->Note( "BYTE note 2", "BYTE" );' . "\n" );
$result = $test->Note( "BYTE note 2", "BYTE" );
print_r( $result );
echo( '$result = $test->Note( "BYTE note 1", "BYTE" );' . "\n" );
$result = $test->Note( "COLOR note 1", "COLOR" );
print_r( $result );
echo( '$result = $test->Note( "COLOR note 2", "COLOR" );' . "\n" );
$result = $test->Note( "COLOR note 2", "COLOR" );
print_r( $result );
print_r( $test );


?>

