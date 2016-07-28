<?php

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");

//
// Init local storage.
//
$source_file = "/Users/milkoskofic/Documents/Development/Git/SMARTSurveyManager/src/StataFile.php";
$dest_file = "/Users/milkoskofic/Desktop/downloaded.php";
$source_document = file_get_contents( $source_file );

//
// Set metadata.
//
$metadata = [ 'Name' => 'name', 'Description' => 'This is a description' ];

//
// Instantiate bucket.
//
$fs =
	new \MongoDB\GridFS\Bucket(
		new \MongoDB\Driver\Manager( "mongodb://localhost:27017" ),
		"TEST_FS"
	);
$fs->drop();

//
// Instantiate read stream.
//
$read = fopen( $source_file, "r" );

//
// Write file.
//
$id = $fs->uploadFromStream( 'filename', $read, [ 'metadata' => $metadata ] );
fclose( $read );
var_dump( $id );

//
// Find document.
//
$cursor = $fs->find( [ "metadata.Name" => "name" ] );
foreach( $cursor as $document )
	print_r( $document[ "metadata" ] );

//
// Instantiate write stream.
//
$write = fopen( $dest_file, "w" );

//
// Download document.
//
$fs->downloadToStream( $id, $write );
fclose( $write );

//
// Assert contents.
//
$ok = ( $source_document == file_get_contents( $dest_file ) );
if( $ok )
	echo( "\nContents match\n" );
else
	echo( "\nERROR\n" );

?>