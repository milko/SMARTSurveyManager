<?php

/**
 * StataFile.php
 *
 * This file contains the definition of the {@link StataFile} class.
 */

/*=======================================================================================
 *																						*
 *									StataFile.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Stata .dta file.</h4>
 *
 * This class implements an object that can read and parse a <em>Stata 14</en> <tt>.dta</tt>
 * data file.
 *
 * The class implements a structure that will be populated with the elements contained in
 * the Stata file, data will be held in the inherited ArrayObject array and other elements
 * in data members.
 *
 *	@package	Stata
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/07/2016
 */
class StataFile extends ArrayObject
{
	/**
	 * <h4>Opening marker.</h4>
	 *
	 * This constant holds the <em>opening file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_OPEN = 'stata_dta';

	/**
	 * <h4>Header marker.</h4>
	 *
	 * This constant holds the <em>header file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_HEADER = 'header';

	/**
	 * <h4>Release marker.</h4>
	 *
	 * This constant holds the <em>release file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_RELEASE = 'release';

	/**
	 * <h4>Byte order marker.</h4>
	 *
	 * This constant holds the <em>byte order file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_BYTE_ORDER = 'byteorder';

	/**
	 * <h4>Variables count marker.</h4>
	 *
	 * This constant holds the <em>variables count file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_COUNT_VARS = 'K';

	/**
	 * <h4>Observations count marker.</h4>
	 *
	 * This constant holds the <em>observations count file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_COUNT_RECS = 'N';

	/**
	 * <h4>Dataset label marker.</h4>
	 *
	 * This constant holds the <em>dataset label file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_LABEL = 'label';

	/**
	 * <h4>File path.</h4>
	 *
	 * This data member holds the <em>file path</em>.
	 *
	 * @var string
	 */
	protected $mPath = NULL;

	/**
	 * <h4>File format ID.</h4>
	 *
	 * This data member holds the <em>file release version</em>.
	 *
	 * @var string
	 */
	protected $mFormat = NULL;

	/**
	 * <h4>Byte order.</h4>
	 *
	 * This data member holds the <em>byte order</em>:
	 *
	 * <ul>
	 * 	<li><tt>MSF</tt>: Most Significant byte First (big endian).
	 * 	<li><tt>LSF</tt>: Least Significant byte First (little endian).
	 * </ul>
	 *
	 * @var string
	 */
	protected $mByteOrder = NULL;

	/**
	 * <h4>Variables count.</h4>
	 *
	 * This data member holds the <em>number of variables</em>:
	 *
	 * @var int
	 */
	protected $mNumVars = NULL;

	/**
	 * <h4>Observations count.</h4>
	 *
	 * This data member holds the <em>number of observations</em>.
	 *
	 * <em>Note that the original value is unsigned, while PHP does not support unsigned
	 * integers: we do not handle the case in which the number would be converted to a
	 * negative value.</em>
	 *
	 * @var int
	 */
	protected $mNumRecs = NULL;

	/**
	 * <h4>Dataset label.</h4>
	 *
	 * This data member holds the <em>dataset label</em>:
	 *
	 * @var string
	 */
	protected $mLabelDataset = NULL;

	/**
	 * <h4>Dataset time stamp.</h4>
	 *
	 * This data member holds the <em>dataset time stamp</em>:
	 *
	 * @var DateTime
	 */
	protected $mTimeStamp = NULL;




/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * <h4>Instantiate class.</h4>
	 *
	 * The object can be instantiated by providing the path to a Stata file.
	 *
	 * @param string				$theFile			File path.
	 */
	public function __construct( $theFile = NULL )
	{
		//
		// Handle file.
		//
		if( $theFile !== NULL )
		{
			//
			// Set file.
			//
			$this->Path( (string)$theFile );

		} // Provided file.

	} // Constructor.



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Path																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve file path.</h4>
	 *
	 * This method can be used to set or retrieve the file path, if you provide a string, it
	 * will be interpreted as the new value, if you provide <tt>NULL</tt>, the method will
	 * return the current value.
	 *
	 * When providing a new path, the method will check if the path points to a file, if the
	 * file is readable and if the file extension is <tt>.dta</tt>.
	 *
	 *
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function Path( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mPath;													// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"File path cannot be deleted." );								// !@! ==>

		//
		// Get file.
		//
		$file = new SplFileObject( (string)$theValue, "r" );

		//
		// Check type.
		//
		if( $file->isFile() )
		{
			//
			// Check if readable.
			//
			if( $file->isReadable() )
			{
				//
				// Check extension.
				//
				if( strtolower( $file->getExtension() ) == 'dta' )
					return $this->mPath = $file->getRealPath();						// ==>

				//
				// Invalid extension.
				//
				else
					throw new InvalidArgumentException(
						"Expected a (.dta) extension." );						// !@! ==>

			} // Is readable.

			//
			// Cannot be read.
			//
			else
				throw new InvalidArgumentException(
					"File [$theValue] cannot be read." );						// !@! ==>

		} // Is a file.

		//
		// Not a file.
		//
		else
			throw new InvalidArgumentException(
				"File [$theValue] is not a file." );							// !@! ==>

	} // Path.


	/*===================================================================================
	 *	Format																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve file format ID.</h4>
	 *
	 * This method can be used to set or retrieve the file format ID, if you provide a
	 * string, it will be interpreted as the new value, if you provide <tt>NULL</tt>, the
	 * method will return the current value.
	 *
	 * In this release we only support version <tt>118</tt>, other versions will trigger an
	 * exception.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function Format( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mFormat;													// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Release cannot be deleted." );									// !@! ==>

		//
		// Check version.
		//
		switch( $theValue )
		{
			case '118':
				return $this->mFormat = (string)$theValue;							// ==>
		}

		throw new InvalidArgumentException(
			"Only version 118 is supported: " .
			"provided [$theValue]." );											// !@! ==>

	} // Format.


	/*===================================================================================
	 *	ByteOrder																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve byte order.</h4>
	 *
	 * This method can be used to set or retrieve the byte order, if you provide a string,
	 * it will be interpreted as the new value, if you provide <tt>NULL</tt>, the method
	 * will return the current value.
	 *
	 * These are the supported values:
	 *
	 * <ul>
	 * 	<li><tt>MSF</tt>: Most Significant byte First (big endian).
	 * 	<li><tt>LSF</tt>: Least Significant byte First (little endian).
	 * </ul>
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function ByteOrder( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mByteOrder;												// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Byte order cannot be deleted." );								// !@! ==>

		//
		// Check order.
		//
		switch( strtoupper( $theValue ) )
		{
			case 'MSF':
			case 'LSF':
				return $this->mByteOrder = strtoupper( $theValue );					// ==>
		}

		throw new InvalidArgumentException(
			"Invalid byte order [$theValue]." );								// !@! ==>

	} // ByteOrder.


	/*===================================================================================
	 *	VariablesCount																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve number of variables.</h4>
	 *
	 * This method can be used to set or retrieve the variables count, if you provide an
	 * integer, it will be interpreted as the new value, if you provide <tt>NULL</tt>, the
	 * method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return int
	 * @throws InvalidArgumentException
	 */
	public function VariablesCount( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mNumVars;													// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Release cannot be deleted." );									// !@! ==>

		//
		// Check value.
		//
		if( is_numeric( $theValue ) )
		{
			//
			// Check sign.
			//
			$theValue = (int)$theValue;
			if( $theValue > 0 )
			{
				//
				// Check variables count.
				//
				if( $theValue > 65535 )
					throw new InvalidArgumentException(
						"Invalid variables count: " .
						"maximum value is 65535, provided [$theValue]." );		// !@! ==>

				return $this->mNumVars = $theValue;									// ==>

			} // Positive.

			//
			// Negative.
			//
			else
				throw new InvalidArgumentException(
					"Invalid variables count: " .
					"expecting a positive number, provided [$theValue]." );		// !@! ==>

		} // Is numeric.

		throw new InvalidArgumentException(
			"Invalid variables count: " .
			"expecting a number, provided [$theValue]." );						// !@! ==>

	} // VariablesCount.


	/*===================================================================================
	 *	ObservationsCount																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve number of observations.</h4>
	 *
	 * This method can be used to set or retrieve the observations count, if you provide a
	 * double, it will be interpreted as the new value, if you provide <tt>NULL</tt>, the
	 * method will return the current value.
	 *
	 * <em>The value is a 64 unsigned integer in Stata, while PHP does not support unsigned
	 * integers, in this method we assume the value should be positive.</em>
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return double
	 * @throws InvalidArgumentException
	 */
	public function ObservationsCount( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mNumRecs;													// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Release cannot be deleted." );									// !@! ==>

		//
		// Check value.
		//
		if( is_numeric( $theValue ) )
		{
			//
			// Check sign.
			//
			$theValue = (int)$theValue;
			if( $theValue > 0 )
				return $this->mNumRecs = $theValue;									// ==>

			//
			// Negative.
			//
			else
				throw new InvalidArgumentException(
					"Invalid variables count: " .
					"expecting a positive number, provided [$theValue]." );		// !@! ==>

		} // Is numeric.

		throw new InvalidArgumentException(
			"Invalid variables count: " .
			"expecting a number, provided [$theValue]." );						// !@! ==>

	} // ObservationsCount.


	/*===================================================================================
	 *	DatasetLabel																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset label.</h4>
	 *
	 * This method can be used to set or retrieve the dataset label, if you provide a
	 * string, it will be interpreted as the new value, if you provide <tt>NULL</tt>, the
	 * method will return the current value, if you provide <tt>FALSE</tt>, the method will
	 * reset the value.
	 *
	 * The label may have at most 80 unicode characters which corresponds to a maximum of
	 * 320 bytes: if the limit is overflowed, the string will be truncated.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return string
	 */
	public function DatasetLabel( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mLabelDataset;											// ==>

		//
		// Reset value.
		//
		if( $theValue === FALSE )
			return $this->mLabelDataset = NULL;										// ==>

		//
		// Truncate string.
		//
		if( mb_strlen( $theValue, 'UTF-8' ) > 80 )
			$theValue = mb_substr( $theValue, 0, 80, 'UTF-8' );

		return $this->mLabelDataset = $theValue;									// ==>

	} // DatasetLabel.


	/*===================================================================================
	 *	TimeStamp																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset time stamp.</h4>
	 *
	 * This method can be used to set or retrieve the dataset time stamp, if you provide a
	 * date, it will be interpreted as the new value, if you provide <tt>NULL</tt>, the
	 * method will return the current value, if you provide <tt>FALSE</tt>, the method will
	 * reset the value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return DateTime
	 */
	public function TimeStamp( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mTimeStamp;												// ==>

		//
		// Reset value.
		//
		if( $theValue === FALSE )
			return $this->mTimeStamp = NULL;										// ==>

		return $this->mTimeStamp = new DateTime( $theValue );						// ==>

	} // TimeStamp.



/*=======================================================================================
 *																						*
 *								PUBLIC FILE INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Read																			*
	 *==================================================================================*/

	/**
	 * <h4>Read the file.</h4>
	 *
	 * This method can be used to read and parse the current Stata file, it will return
	 * <tt>TRUE</tt> if the file was parsed and <tt>NULL</tt> if no file was declared; any
	 * error will raise an exception.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return mixed				<tt>TRUE</tt> successful, <tt>NULL</tt> no file.
	 */
	public function Read()
	{
		//
		// Handle file.
		//
		$file = $this->Path();
		if( $file !== NULL )
		{
			//
			// Get file.
			//
			$file = new SplFileObject( $file, "r" );

			//
			// Read header.
			//
			$this->readHeader( $file );

		} // Declared file.

		return NULL;																// ==>

	} // Read.



/*=======================================================================================
 *																						*
 *								PROTECTED PARSING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	readHeader																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the file header.</h4>
	 *
	 * This method can be used to read and parse the current Stata file, it will return
	 * <tt>TRUE</tt> if the file was parsed and <tt>NULL</tt> if no file was declared; any
	 * error will raise an exception.
	 *
	 * <em>Note: we are reading fixed lengths for the following elements:
	 *
	 * <ul>
	 * 	<li><tt>release</tt>: 3.
	 * 	<li><tt>byteorder</tt>: 3.
	 * 	<li><tt>K</tt>: 4.
	 * 	<li><tt>N</tt>: 8.
	 * </ul></em>
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return mixed				<tt>TRUE</tt> successful, <tt>NULL</tt> no file.
	 * @throws RuntimeException
	 */
	public function readHeader( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_OPEN, FALSE );

		//
		// Get header token.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER, FALSE );

		//
		// Get release.
		//
		$this->readToken( $theFile, self::kTOKEN_RELEASE, FALSE );
		$this->Format( $theFile->fread( 3 ) );
		$this->readToken( $theFile, self::kTOKEN_RELEASE, TRUE );

		//
		// Get byte order.
		//
		$this->readToken( $theFile, self::kTOKEN_BYTE_ORDER, FALSE );
		$this->ByteOrder( $theFile->fread( 3 ) );
		$this->readToken( $theFile, self::kTOKEN_BYTE_ORDER, TRUE );

		//
		// Get variables count.
		//
		$this->readToken( $theFile, self::kTOKEN_COUNT_VARS, FALSE );
		$this->VariablesCount( $this->readUShort( $theFile ) );
		$this->readToken( $theFile, self::kTOKEN_COUNT_VARS, TRUE );

		//
		// Get observations count.
		//
		$this->readToken( $theFile, self::kTOKEN_COUNT_RECS, FALSE );
		$this->ObservationsCount( $this->readULong( $theFile ) );
		$this->readToken( $theFile, self::kTOKEN_COUNT_RECS, TRUE );

		//
		// Get dataset label.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_LABEL, FALSE );
		$this->DatasetLabel( $this->readBString( $theFile ) );
		$this->readToken( $theFile, self::kTOKEN_DATASET_LABEL, TRUE );

	} // readHeader.



/*=======================================================================================
 *																						*
 *								PROTECTED READING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	readToken																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the file header.</h4>
	 *
	 * This method can be used to read a token and check whether it is correct.
	 *
	 * The method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theFile</b>: The file object.
	 * 	<li><b>$theToken</b>: The token to read: it is the content of the token without the
	 * 		opening and closing matkers.
	 * 	<li><b>$doClose</b>: <tt>TRUE</tt> means closing token, <tt>FALSE</tt> opening
	 * 		token.
	 * </ul>
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @param string				$theToken			Token to read.
	 * @param bool					$doClose			<tt>TRUE</tt> closing tag.
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 */
	public function readToken( SplFileObject $theFile,
							   string		 $theToken,
							   bool			 $doClose = FALSE )
	{
		//
		// Set token.
		//
		$token = ( $doClose )
			? ('<' . $theToken . '>')
			: ('</' . $theToken . '>');

		//
		// Read token.
		//
		$tmp = $theFile->fread( strlen( $token ) );
		if( $tmp === FALSE )
			throw new RuntimeException(
				"Unable to read file token [$token]." );						// !@! ==>

		//
		// Check token.
		//
		if( $tmp != $token )
			throw new InvalidArgumentException(
				"Invalid file token [$tmp]." );									// !@! ==>

	} // readToken.


	/*===================================================================================
	 *	readUShort																		*
	 *==================================================================================*/

	/**
	 * <h4>Read unsigned short.</h4>
	 *
	 * This method can be used to read an unsigned short.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return int					The integer value.
	 * @throws RuntimeException
	 */
	public function readUShort( SplFileObject $theFile )
	{
		//
		// Read value.
		//
		$data = $theFile->fread( 2 );
		if( $data === FALSE )
			throw new RuntimeException(
				"Unable to read unsigned short." );								// !@! ==>

		//
		// Unpack.
		//
		switch( $tmp = $this->ByteOrder() )
		{
			case 'MSF':
				return unpack( 'n', $data );										// ==>

			case 'LSF':
				return unpack( 'v', $data );										// ==>
		}

		throw new RuntimeException(
			"Invalid byte order [$tmp]." );										// !@! ==>

	} // readUShort.


	/*===================================================================================
	 *	readULong																		*
	 *==================================================================================*/

	/**
	 * <h4>Read unsigned long.</h4>
	 *
	 * This method can be used to read an unsigned long.
	 *
	 * <em>Note that the method returns a double, since PHP does not handle unsigned
	 * integers.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return int					The signed integer.
	 * @throws RuntimeException
	 */
	public function readULong( SplFileObject $theFile )
	{
		//
		// Read value.
		//
		$data = $theFile->fread( 8 );
		if( $data === FALSE )
			throw new RuntimeException(
				"Unable to read unsigned long." );								// !@! ==>

		//
		// Unpack.
		//
		switch( $tmp = $this->ByteOrder() )
		{
			case 'MSF':
				return unpack( 'J', $data );										// ==>

			case 'LSF':
				return unpack( 'P', $data );										// ==>
		}

		throw new RuntimeException(
			"Invalid byte order [$tmp]." );										// !@! ==>

	} // readULong.


	/*===================================================================================
	 *	readBString																		*
	 *==================================================================================*/

	/**
	 * <h4>Read byte string.</h4>
	 *
	 * This method can be used to read a string prefixed by a length byte.
	 *
	 * The lenght value contains the number of byte in the string, while the string is an
	 * UTF-8 string, so a character may be made up of at most 4 bytes.
	 *
	 * If there is no label, the method will return <tt>FALSE</tt>.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return mixed				The string or <tt>FALSE</tt>.
	 * @throws RuntimeException
	 */
	public function readBString( SplFileObject $theFile )
	{
		//
		// Read length.
		//
		$length = $this->readUShort( $theFile );
		if( $length )
		{
			//
			// Read string.
			//
			$data = $theFile->fread( $length );
			if( $data === FALSE )
				throw new RuntimeException(
					"Unable to read string." );									// !@! ==>

			return $data;															// ==>

		} // Has label.

		return FALSE;																// ==>

	} // readBString.


	/*===================================================================================
	 *	readTimeStamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Read time stamp.</h4>
	 *
	 * This method can be used to read the dataset time stamp.
	 *
	 * If there is no label, the method will return <tt>FALSE</tt>.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return mixed				The time stamp string or <tt>FALSE</tt>.
	 * @throws RuntimeException
	 */
	public function readTimeStamp( SplFileObject $theFile )
	{
		//
		// Read type.
		//
		$type = $theFile->fread( 1 );
		if( $type === FALSE )
			throw new RuntimeException(
				"Unable to read byte." );										// !@! ==>
		$type = unpack( 'C', $type );

		//
		// Parse time stamp.
		//
		switch( $type )
		{
			case 0:
				return FALSE;														// ==>

			case 17:
				$data = $theFile->fread( 17 );
				if( $data === FALSE )
					throw new RuntimeException(
						"Unable to read time stamp." );							// !@! ==>

				return trim( $data );												// ==>

		} // Has time stamp.

		throw new RuntimeException(
			"Invalid time stamp type [$type]." );								// !@! ==>

	} // readTimeStamp.




} // class StataFile.


?>
