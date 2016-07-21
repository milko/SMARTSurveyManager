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
	const kTOKEN_DATASET_OPEN = 'stata_dta';

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
	const kTOKEN_HEADER_RELEASE = 'release';

	/**
	 * <h4>Byte order marker.</h4>
	 *
	 * This constant holds the <em>byte order file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_HEADER_ORDER = 'byteorder';

	/**
	 * <h4>Variables count marker.</h4>
	 *
	 * This constant holds the <em>variables count file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_HEADER_VARS = 'K';

	/**
	 * <h4>Observations count marker.</h4>
	 *
	 * This constant holds the <em>observations count file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_HEADER_RECS = 'N';

	/**
	 * <h4>Dataset label marker.</h4>
	 *
	 * This constant holds the <em>dataset label file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_HEADER_LABEL = 'label';

	/**
	 * <h4>Dataset time stamp marker.</h4>
	 *
	 * This constant holds the <em>dataset time stamp file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_HEADER_STAMP = 'timestamp';

	/**
	 * <h4>Dataset map marker.</h4>
	 *
	 * This constant holds the <em>dataset map file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_MAP = 'map';

	/**
	 * <h4>Dataset variable types marker.</h4>
	 *
	 * This constant holds the <em>dataset variable types file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_TYPES = 'variable_types';

	/**
	 * <h4>Dataset variable names marker.</h4>
	 *
	 * This constant holds the <em>dataset variable names file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_NAMES = 'varnames';

	/**
	 * <h4>Dataset sort list marker.</h4>
	 *
	 * This constant holds the <em>dataset sort list file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_SORT = 'sortlist';

	/**
	 * <h4>Dataset formats list marker.</h4>
	 *
	 * This constant holds the <em>dataset formats list file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_FORMATS = 'formats';

	/**
	 * <h4>Dataset value labels marker.</h4>
	 *
	 * This constant holds the <em>dataset value labels file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_VALABNAMES = 'value_label_names';

	/**
	 * <h4>Dataset variable labels marker.</h4>
	 *
	 * This constant holds the <em>dataset variable labels file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_VARLABEL = 'variable_labels';

	/**
	 * <h4>Dataset characteristics marker.</h4>
	 *
	 * This constant holds the <em>dataset characteristics file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_CHARACTERISTICS = 'characteristics';

	/**
	 * <h4>Dataset characteristics element marker.</h4>
	 *
	 * This constant holds the <em>dataset characteristics element file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_CHARACTERISTICS_ELM = 'ch';

	/**
	 * <h4>Dataset data marker.</h4>
	 *
	 * This constant holds the <em>dataset data file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_DATA = 'data';

	/**
	 * <h4>Dataset long strings marker.</h4>
	 *
	 * This constant holds the <em>dataset long strings file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_LSTRING = 'strls';

	/**
	 * <h4>Dataset value labels marker.</h4>
	 *
	 * This constant holds the <em>dataset value labels file marker</em>.
	 *
	 * @var string
	 */
	const kTOKEN_DATASET_VALLABEL = 'value_labels';

	/**
	 * <h4>Dataset close token.</h4>
	 *
	 * This constant holds the <em>dataset close token</em>.
	 *
	 * @var string
	 */
	const kTOKEN_CLOSE = 'CLOSE';

	/**
	 * <h4>Dataset end of file token.</h4>
	 *
	 * This constant holds the <em>dataset end of file token</em>.
	 *
	 * @var string
	 */
	const kTOKEN_EOF = 'EOF';

	/**
	 * <h4>Variable name offset.</h4>
	 *
	 * This constant holds the <em>variable name offset</em> in the data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_NAME = 'name';

	/**
	 * <h4>Variable label offset.</h4>
	 *
	 * This constant holds the <em>variable label offset</em> in the data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_LABEL = 'label';

	/**
	 * <h4>Variable type offset.</h4>
	 *
	 * This constant holds the <em>variable type offset</em> in the data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_TYPE = 'type';

	/**
	 * <h4>Variable format offset.</h4>
	 *
	 * This constant holds the <em>variable format offset</em> in the data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_FORMAT = 'format';

	/**
	 * <h4>Variable sort order offset.</h4>
	 *
	 * This constant holds the <em>variable sort order offset</em> in the data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_SORT = 'sort';

	/**
	 * <h4>Variable enumeration offset.</h4>
	 *
	 * This constant holds the <em>variable enumeration offset</em> in the data dictionary,
	 * this element contains an array structured as following:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_ENUM_NAME}</tt>: The controlled vocabulary name.
	 * 	<li><tt>{@link kOFFSET_ENUM_ELMS}</tt>: The controlled vocabulary elements.
	 * </ul>
	 *
	 * @var string
	 */
	const kOFFSET_ENUM = 'enum';

	/**
	 * <h4>Controlled vocabulary name offset.</h4>
	 *
	 * This constant holds the <em>comtrolled vocabulary name offset</em> in the data
	 * dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_ENUM_NAME = 'name';

	/**
	 * <h4>Controlled vocabulary elements offset.</h4>
	 *
	 * This constant holds the <em>comtrolled vocabulary elements offset</em> in the data
	 * dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_ENUM_ELMS = 'elms';

	/**
	 * <h4>Variable characteristics size offset.</h4>
	 *
	 * This constant holds the <em>variable characteristics record size offset</em> in the
	 * data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_CHARS_SIZE = 'size';

	/**
	 * <h4>Variable characteristics variable offset.</h4>
	 *
	 * This constant holds the <em>variable characteristics variable name offset</em> in the
	 * data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_CHARS_VARNAME = 'var';

	/**
	 * <h4>Variable characteristics name offset.</h4>
	 *
	 * This constant holds the <em>variable characteristics name offset</em> in the
	 * data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_CHARS_NAME = 'name';

	/**
	 * <h4>Variable characteristics data offset.</h4>
	 *
	 * This constant holds the <em>variable characteristics data offset</em> in the
	 * data dictionary.
	 *
	 * @var string
	 */
	const kOFFSET_CHARS_DATA = 'data';

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
	 * This data member holds the <em>number of variables</em>.
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
	 * This data member holds the <em>dataset label</em>.
	 *
	 * @var string
	 */
	protected $mLabelDataset = NULL;

	/**
	 * <h4>Dataset time stamp.</h4>
	 *
	 * This data member holds the <em>dataset time stamp</em>.
	 *
	 * @var DateTime
	 */
	protected $mTimeStamp = NULL;

	/**
	 * <h4>Dataset map.</h4>
	 *
	 * This data member holds the <em>dataset map</em>.
	 *
	 * @var array
	 */
	protected $mMap = [];

	/**
	 * <h4>Data dictionary.</h4>
	 *
	 * This data member holds the <em>data dictionary</em>, it is an array structured as
	 * follows:
	 *
	 * <ul>
	 * 	<li><i>index</i>: The variable index.
	 * 	<li><i>value</i>: The variable information:
	 * 	 <ul>
	 * 		<li><tt>kOFFSET_NAME</tt>: Variable name.
	 * 		<li><tt>kOFFSET_LABEL</tt>: Variable label.
	 * 		<li><tt>kOFFSET_TYPE</tt>: Variable data type.
	 * 		<li><tt>kOFFSET_FORMAT</tt>: Variable data format.
	 * 	 </ul>
	 * </ul>
	 *
	 * @var array
	 */
	protected $mDict = [];

	/**
	 * <h4>Characteristics.</h4>
	 *
	 * This data member holds the <em>characteristics</em>, it is an array of arrays
	 * structured as follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_CHARS_SIZE}</tt>: The characteristics record size.
	 * 	<li><tt>{@link kOFFSET_CHARS_VARNAME}</tt>: The characteristics variable name.
	 * 	<li><tt>{@link kOFFSET_CHARS_NAME}</tt>: The characteristics name.
	 * 	<li><tt>{@link kOFFSET_CHARS_DATA}</tt>: The characteristics data.
	 * </ul>
	 *
	 * @var array
	 */
	protected $mChars = [];




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
	 * This method will reset the data members to their idle state.
	 *
	 * @uses headerInit()
	 * @uses mapInit()
	 */
	public function __construct()
	{
		//
		// Reset members.
		//
		$this->headerInit();
		$this->mapInit();

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
	 * return the current value and if you provide <tt>FALSE</tt>, the method will reset the
	 * value to an idle state.
	 *
	 * The second argument is a flag that indicates whether the file is to be created
	 * (<tt>TRUE</tt>).
	 *
	 * When providing a new path, the method will check if the path points to a file, if the
	 * file is readable or writable, depending on the second paraneter, and if the file
	 * extension is <tt>.dta</tt>.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @param bool					$doCreate			<tt>TRUE</tt> create file.
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function Path( $theValue = NULL, bool $doCreate = FALSE )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mPath;													// ==>

		//
		// Reset path.
		//
		if( $theValue === FALSE )
			return $this->mPath = NULL;												// ==>

		//
		// Get file.
		//
		$file = new SplFileObject( (string)$theValue, ( $doCreate ) ? 'w+' : 'r' );

		//
		// Check type.
		//
		if( $file->isFile() )
		{
			//
			// Check if readable or writable.
			//
			if( ( $doCreate
			   && $file->isWritable() )
			 || ( (! $doCreate)
			   && $file->isReadable() ) )
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

			} // Is readable or writable.

			//
			// Cannot be read.
			//
			else
				throw new InvalidArgumentException(
					"File [$theValue] cannot be " .
					( $doCreate ) ? 'written.' : 'read.' );						// !@! ==>

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
	 * method will return the current value and if you provide <tt>FALSE</tt>, the method
	 * will reset the value to an idle state.
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
			return $this->mFormat = NULL;											// ==>

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
	 * will return the current value and if you provide <tt>FALSE</tt>, the method will
	 * reset the value to an idle state.
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
			return $this->mByteOrder = NULL;										// ==>

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
	 * method will return the current value and if you provide <tt>FALSE</tt>, the method
	 * will reset the value to an idle state.
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
			return $this->mNumVars = NULL;											// ==>

		//
		// Check value.
		//
		if( is_numeric( $theValue ) )
		{
			//
			// Check sign.
			//
			$theValue = (int)$theValue;
			if( $theValue >= 0 )
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
	 * method will return the current value and if you provide <tt>FALSE</tt>, the method
	 * will reset the value to an idle state.
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
			return $this->mNumRecs = NULL;											// ==>

		//
		// Check value.
		//
		if( is_numeric( $theValue ) )
		{
			//
			// Check sign.
			//
			$theValue = (int)$theValue;
			if( $theValue >= 0 )
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
	 * reset the value to an idle state.
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
			return $this->mLabelDataset = '';										// ==>

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
	 * reset the value to an idle state.
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


	/*===================================================================================
	 *	VariableType																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset variable type(s).</h4>
	 *
	 * This method can be used to set or retrieve the dataset variable type(s), the method
	 * expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable index or <tt>NULL</tt> for all variables.
	 * 	<li><b>$theValue</b>: The new variable type, list or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value.
	 * 		<li><tt>array</tt>: Set all values; <em>it is assumed the full list was
	 * 			provided</em>.
	 * 		<li><em>other</em>: Set the new value related to the provided variable index and
	 * 			parsed according to the next parameter.
	 * 	 </ul>
	 * 	<li><b>$asName</b>: If <tt>TRUE</tt> it is assumed the type is provided as the human
	 * 		readable type name, if <tt>FALSE</tt>, the type should be an integer as stored
	 * 		in the Stata file. This also applies when retrieving values.
	 * </ul>
	 *
	 * @param int					$theVariable		Variable index or <tt>NULL</tt>.
	 * @param mixed					$theValue			New value, or operation.
	 * @param bool					$asName				<tt>TRUE</tt> type name.
	 * @return mixed				Variable name or all names.
	 * @throws InvalidArgumentException
	 */
	public function VariableType(	   $theVariable = NULL,
								  	   $theValue = NULL,
								  bool $asName = FALSE )
	{
		//
		// Set all types.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate types.
			//
			$list = [];
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->VariableType( $key, $value, $asName );

			return $list;															// ==>

		} // Set all types.

		//
		// Get all types.
		//
		if( ($theValue === NULL)
		 && ($theVariable === NULL) )
		{
			//
			// Iterate data dictionary.
			//
			$list = [];
			foreach( array_keys( $this->mDict ) as $key )
				$list[ $key ]
					= $this->VariableType( $key, NULL, $asName );

			return $list;															// ==>

		} // Get all types.

		//
		// Check variable index.
		//
		if( ! is_numeric( $theVariable ) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>
		$theVariable = (int)$theVariable;
		if( ($theVariable < 0)
		 || ($theVariable > $this->VariablesCount()) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>

		//
		// Return current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check variable index.
			//
			if( $theVariable >= count( $this->mDict ) )
				throw new InvalidArgumentException(
					"Invalid variable index [$theVariable]." );					// !@! ==>

			//
			// Get value.
			//
			$value = $this->mDict[ $theVariable ][ self::kOFFSET_TYPE ];

			//
			// Handle code.
			//
			if( $asName )
				return $this->parseType( $value );									// ==>

			return $value;															// ==>

		} // Return current value.

		//
		// Convert or check type.
		//
		$type = ( $asName )
			  ? $this->parseType( (string)$theValue )
			  : $this->parseType( (int)$theValue );

		//
		// Set member.
		//
		$this->mDict[ $theVariable ][ self::kOFFSET_TYPE ]
			= ( $asName ) ? $type : (int)$theValue;

		return ( $asName ) ? (string)$theValue										// ==>
						   : (int)$theValue;										// ==>

	} // VariableType.


	/*===================================================================================
	 *	VariableName																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset variable name(s).</h4>
	 *
	 * This method can be used to set or retrieve the dataset variable name(s), the method
	 * expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable index or <tt>NULL</tt> for all variables.
	 * 	<li><b>$theValue</b>: The new variable name, list or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value.
	 * 		<li><tt>string</tt>: Set the new value related to the provided variable index.
	 * 		<li><tt>array</tt>: Set all values; <em>it is assumed the full list was
	 * 			provided</em>.
	 * 	 </ul>
	 * </ul>
	 *
	 * @param int					$theVariable		Variable index or <tt>NULL</tt>.
	 * @param mixed					$theValue			New value, or operation.
	 * @return mixed				Variable name or all names.
	 * @throws InvalidArgumentException
	 */
	public function VariableName( $theVariable = NULL, $theValue = NULL )
	{
		//
		// Set all names.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate names.
			//
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->VariableName( $key, $value );

			return $theValue;														// ==>

		} // Set all names.

		//
		// Get all names.
		//
		if( ($theValue === NULL)
			&& ($theVariable === NULL) )
		{
			//
			// Iterate data dictionary.
			//
			$list = [];
			foreach( array_keys( $this->mDict ) as $key )
				$list[ $key ]
					= $this->mDict[ $key ][ self::kOFFSET_NAME ];

			return $list;															// ==>

		} // Get all names.

		//
		// Check variable index.
		//
		if( ! is_numeric( $theVariable ) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>
		$theVariable = (int)$theVariable;
		if( ($theVariable < 0)
		 || ($theVariable > $this->VariablesCount()) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>

		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mDict[ $theVariable ][ self::kOFFSET_NAME ];				// ==>

		return
			$this->mDict[ $theVariable ][ self::kOFFSET_NAME ]
				= mb_substr( (string)$theValue, 0, 32, 'UTF-8' );					// ==>

	} // VariableName.


	/*===================================================================================
	 *	VariableSort																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset variable sort order(s).</h4>
	 *
	 * This method can be used to set or retrieve the dataset sort order(s), the method
	 * expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable index, name or <tt>NULL</tt> for all variables.
	 * 	<li><b>$theValue</b>: The new sort order, list or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value.
	 * 		<li><tt>string</tt>: Set the new value related to the provided variable index.
	 * 		<li><tt>array</tt>: Set all values; <em>it is assumed the full list was
	 * 			provided</em>.
	 * 	 </ul>
	 * 	<li><b>$asName</b>: If <tt>TRUE</tt> it is assumed the variable is provided by name,
	 * 		if not, it is assumed the variable(s) are provided as the variable index (int).
	 * </ul>
	 *
	 * @param int					$theVariable		Variable index or <tt>NULL</tt>.
	 * @param mixed					$theValue			New value, or operation.
	 * @param bool					$asName				<tt>TRUE</tt> variable name(s).
	 * @return mixed				Sort order or all orders.
	 * @throws InvalidArgumentException
	 */
	public function VariableSort(	   $theVariable = NULL,
									   $theValue = NULL,
								  bool $asName = FALSE )
	{
		//
		// Set all names.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate orders.
			//
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->VariableSort( $key, $value, $asName );

			return $theValue;														// ==>

		} // Set all orders.

		//
		// Get all orders.
		//
		if( ($theValue === NULL)
		 && ($theVariable === NULL) )
		{
			//
			// Iterate data dictionary.
			//
			$list = [];
			foreach( array_keys( $this->mDict ) as $key )
			{
				//
				// Check if it has sort order.
				//
				if( array_key_exists( self::kOFFSET_SORT, $this->mDict[ $key ] ) )
				{
					//
					// Handle names.
					//
					if( $asName )
						$list[ $this->mDict[ $key ][ self::kOFFSET_NAME ] ]
							= $this->mDict[ $key ][ self::kOFFSET_SORT ];

					//
					// Handle indexes.
					//
					else
						$list[ $key ]
							= $this->mDict[ $key ][ self::kOFFSET_SORT ];

				} // Has sort order.

			} // Iterating data dictionary.

			return $list;															// ==>

		} // Get all orders.

		//
		// Convert variable name to index.
		//
		if( ! is_int( $theVariable ) )
		{
			$tmp = $this->parseName( $theVariable );
			if( $tmp === NULL )
				throw new InvalidArgumentException(
					"Unknown variable name [$theVariable]." );					// !@! ==>
			$theVariable = (int)$tmp;
		}

		//
		// Check variable index.
		//
		if( ($theVariable < 0)
		 || ($theVariable > $this->VariablesCount()) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>

		//
		// Return current value.
		//
		if( $theValue === NULL )
		{
			//
			// Return sort order.
			//
			if( array_key_exists( self::kOFFSET_SORT, $this->mDict[ $theVariable ] ) )
				return $this->mDict[ $theVariable ][ self::kOFFSET_SORT ];			// ==>

			return NULL;															// ==>

		} // Return current value.

		//
		// Check new value.
		//
		if( ! is_numeric( $theValue ) )
			throw new InvalidArgumentException(
				"Invalid sort order [$theValue]: " .
				"expecting an integer." );										// !@! ==>

		return
			$this->mDict[ $theVariable ][ self::kOFFSET_SORT ]
				= (int)$theValue;													// ==>

	} // VariableSort.


	/*===================================================================================
	 *	VariableFormat																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset variable format(s).</h4>
	 *
	 * This method can be used to set or retrieve the dataset variable format(s), the method
	 * expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable index or <tt>NULL</tt> for all variables.
	 * 	<li><b>$theValue</b>: The new variable format, list or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value.
	 * 		<li><tt>string</tt>: Set the new value related to the provided variable index.
	 * 		<li><tt>array</tt>: Set all values; <em>it is assumed the full list was
	 * 			provided</em>.
	 * 	 </ul>
	 * 	<li><b>$asName</b>: If <tt>TRUE</tt> it is assumed the variable is provided by name,
	 * 		if not, it is assumed the variable(s) are provided as the variable index (int).
	 * </ul>
	 *
	 * @param int					$theVariable		Variable index or <tt>NULL</tt>.
	 * @param mixed					$theValue			New value, or operation.
	 * @param bool					$asName				<tt>TRUE</tt> variable name(s).
	 * @return mixed				Variable format or all formats.
	 * @throws InvalidArgumentException
	 */
	public function VariableFormat(		 $theVariable = NULL,
											$theValue = NULL,
											bool $asName = FALSE )
	{
		//
		// Set all formats.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate formats.
			//
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->VariableFormat( $key, $value, $asName );

			return $theValue;														// ==>

		} // Set all formats.

		//
		// Get all formats.
		//
		if( ($theValue === NULL)
			&& ($theVariable === NULL) )
		{
			//
			// Iterate data dictionary.
			//
			$list = [];
			foreach( array_keys( $this->mDict ) as $key )
			{
				//
				// Handle names.
				//
				if( $asName )
					$list[ $this->mDict[ $key ][ self::kOFFSET_NAME ] ]
						= $this->mDict[ $key ][ self::kOFFSET_FORMAT ];

				//
				// Handle indexes.
				//
				else
					$list[ $key ]
						= $this->mDict[ $key ][ self::kOFFSET_FORMAT ];

			} // Iterating data dictionary.

			return $list;															// ==>

		} // Get all formats.

		//
		// Convert variable name to index.
		//
		if( ! is_int( $theVariable ) )
		{
			$tmp = $this->parseName( $theVariable );
			if( $tmp === NULL )
				throw new InvalidArgumentException(
					"Unknown variable name [$theVariable]." );					// !@! ==>
			$theVariable = (int)$tmp;
		}

		//
		// Check variable index.
		//
		if( ($theVariable < 0)
			|| ($theVariable > $this->VariablesCount()) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>

		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mDict[ $theVariable ][ self::kOFFSET_FORMAT ];			// ==>

		return
			$this->mDict[ $theVariable ][ self::kOFFSET_FORMAT ]
				= mb_substr( (string)$theValue, 0, 56, '8bit' );					// ==>

	} // VariableFormat.


	/*===================================================================================
	 *	VariableLabel																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset variable label(s).</h4>
	 *
	 * This method can be used to set or retrieve the dataset variable label(s), the method
	 * expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable index or <tt>NULL</tt> for all variables.
	 * 	<li><b>$theValue</b>: The new variable label, list or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value.
	 * 		<li><tt>string</tt>: Set the new value related to the provided variable index.
	 * 		<li><tt>array</tt>: Set all values; <em>it is assumed the full list was
	 * 			provided</em>.
	 * 	 </ul>
	 * 	<li><b>$asName</b>: If <tt>TRUE</tt> it is assumed the variable is provided by name,
	 * 		if not, it is assumed the variable(s) are provided as the variable index (int).
	 * </ul>
	 *
	 * @param int					$theVariable		Variable index or <tt>NULL</tt>.
	 * @param mixed					$theValue			New value, or operation.
	 * @param bool					$asName				<tt>TRUE</tt> variable name(s).
	 * @return mixed				Variable label or all labels.
	 * @throws InvalidArgumentException
	 */
	public function VariableLabel(		 $theVariable = NULL,
										 $theValue = NULL,
									bool $asName = FALSE )
	{
		//
		// Set all labels.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate labels.
			//
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->VariableLabel( $key, $value, $asName );

			return $theValue;														// ==>

		} // Set all labels.

		//
		// Get all labels.
		//
		if( ($theValue === NULL)
		 && ($theVariable === NULL) )
		{
			//
			// Iterate data dictionary.
			//
			$list = [];
			foreach( array_keys( $this->mDict ) as $key )
			{
				//
				// Handle names.
				//
				if( $asName )
					$list[ $this->mDict[ $key ][ self::kOFFSET_NAME ] ]
						= $this->mDict[ $key ][ self::kOFFSET_LABEL ];

				//
				// Handle indexes.
				//
				else
					$list[ $key ]
						= $this->mDict[ $key ][ self::kOFFSET_LABEL ];

			} // Iterating data dictionary.

			return $list;															// ==>

		} // Get all labels.

		//
		// Convert variable name to index.
		//
		if( ! is_int( $theVariable ) )
		{
			$tmp = $this->parseName( $theVariable );
			if( $tmp === NULL )
				throw new InvalidArgumentException(
					"Unknown variable name [$theVariable]." );					// !@! ==>
			$theVariable = (int)$tmp;
		}

		//
		// Check variable index.
		//
		if( ($theVariable < 0)
		 || ($theVariable > $this->VariablesCount()) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>

		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mDict[ $theVariable ][ self::kOFFSET_LABEL ];				// ==>

		return
			$this->mDict[ $theVariable ][ self::kOFFSET_LABEL ]
				= mb_substr( (string)$theValue, 0, 320, '8bit' );					// ==>

	} // VariableLabel.


	/*===================================================================================
	 *	VariableEnumName																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset variable enumeration name(s).</h4>
	 *
	 * This method can be used to set or retrieve the dataset enumeration name(s), the
	 * method expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable index, name or <tt>NULL</tt> for all variables.
	 * 	<li><b>$theValue</b>: The new enumeration name, list or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value.
	 * 		<li><tt>string</tt>: Set the new value related to the provided variable index.
	 * 		<li><tt>array</tt>: Set all values; <em>it is assumed the full list was
	 * 			provided</em>.
	 * 	 </ul>
	 * 	<li><b>$asName</b>: If <tt>TRUE</tt> it is assumed the variable is provided by name,
	 * 		if not, it is assumed the variable(s) are provided as the variable index (int).
	 * </ul>
	 *
	 * @param int					$theVariable		Variable index or <tt>NULL</tt>.
	 * @param mixed					$theValue			New value, or operation.
	 * @param bool					$asName				<tt>TRUE</tt> variable name(s).
	 * @return mixed				Enumeration name or all enumeration names.
	 * @throws InvalidArgumentException
	 */
	public function VariableEnumName(	   $theVariable = NULL,
										   $theValue = NULL,
									  bool $asName = FALSE )
	{
		//
		// Set all names.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate enumerations.
			//
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->VariableEnumName( $key, $value, $asName );

			return $theValue;														// ==>

		} // Set all names.

		//
		// Get all names.
		//
		if( ($theValue === NULL)
			&& ($theVariable === NULL) )
		{
			//
			// Iterate data dictionary.
			//
			$list = [];
			foreach( array_keys( $this->mDict ) as $key )
			{
				//
				// Check if it has enumeration.
				//
				if( array_key_exists( self::kOFFSET_ENUM, $this->mDict[ $key ] ) )
				{
					//
					// Handle names.
					//
					if( $asName )
						$list[ $this->mDict[ $key ][ self::kOFFSET_NAME ] ]
							= $this->mDict[ $key ]
										  [ self::kOFFSET_ENUM ]
										  [ self::kOFFSET_ENUM_NAME ];

					//
					// Handle indexes.
					//
					else
						$list[ $key ]
							= $this->mDict[ $key ]
										  [ self::kOFFSET_ENUM ]
										  [ self::kOFFSET_ENUM_NAME ];

				} // Has enumeration name.

			} // Iterating data dictionary.

			return $list;															// ==>

		} // Get all enumerations.

		//
		// Convert variable name to index.
		//
		if( ! is_int( $theVariable ) )
		{
			$tmp = $this->parseName( $theVariable );
			if( $tmp === NULL )
				throw new InvalidArgumentException(
					"Unknown variable name [$theVariable]." );					// !@! ==>
			$theVariable = (int)$tmp;
		}

		//
		// Check variable index.
		//
		if( ($theVariable < 0)
			|| ($theVariable > $this->VariablesCount()) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>

		//
		// Return current value.
		//
		if( $theValue === NULL )
		{
			//
			// Return enumeration name.
			//
			if( array_key_exists( self::kOFFSET_ENUM, $this->mDict[ $theVariable ] ) )
				return $this->mDict[ $theVariable ]
								   [ self::kOFFSET_ENUM ]
								   [ self::kOFFSET_ENUM_NAME ];						// ==>

			return NULL;															// ==>

		} // Return current value.

		//
		// Create enumeration record.
		//
		if( ! array_key_exists( self::kOFFSET_ENUM, $this->mDict[ $theVariable ] ) )
			$this->mDict[ $theVariable ][ self::kOFFSET_ENUM ]
				= [];

		return
			$this->mDict[ $theVariable ]
						[ self::kOFFSET_ENUM ]
						[ self::kOFFSET_ENUM_NAME ]
				= (string)$theValue;												// ==>

	} // VariableEnumName.


	/*===================================================================================
	 *	VariableEnumVals																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve the dataset variable enumeration value(s).</h4>
	 *
	 * This method can be used to set or retrieve the dataset enumeration value(s), the
	 * method expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable index, name or <tt>NULL</tt> for all variables.
	 * 	<li><b>$theValue</b>: The new enumeration values list or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value.
	 * 		<li><tt>array</tt>: Set the new values list related to the provided variable
	 * 			index.
	 * 		<li><em>other</em>: Any other type will be interpreted as a list with a single
	 * 			value.
	 * 	 </ul>
	 * 	<li><b>$asName</b>: If <tt>TRUE</tt> it is assumed the variable is provided by name,
	 * 		if not, it is assumed the variable(s) are provided as the variable index (int).
	 * </ul>
	 *
	 * @param int					$theVariable		Variable index or <tt>NULL</tt>.
	 * @param mixed					$theValue			New value, or operation.
	 * @param bool					$asName				<tt>TRUE</tt> variable name(s).
	 * @return mixed				Enumeration name or all enumeration values.
	 * @throws InvalidArgumentException
	 */
	public function VariableEnumVals(	   $theVariable = NULL,
											$theValue = NULL,
											bool $asName = FALSE )
	{
		//
		// Get all names.
		//
		if( ($theValue === NULL)
			&& ($theVariable === NULL) )
		{
			//
			// Iterate data dictionary.
			//
			$list = [];
			foreach( array_keys( $this->mDict ) as $key )
			{
				//
				// Check if it has enumeration.
				//
				if( array_key_exists( self::kOFFSET_ENUM, $this->mDict[ $key ] ) )
				{
					//
					// Handle names.
					//
					if( $asName )
						$list[ $this->mDict[ $key ][ self::kOFFSET_NAME ] ]
							= $this->mDict[ $key ]
						[ self::kOFFSET_ENUM ]
						[ self::kOFFSET_ENUM_ELMS ];

					//
					// Handle indexes.
					//
					else
						$list[ $key ]
							= $this->mDict[ $key ]
						[ self::kOFFSET_ENUM ]
						[ self::kOFFSET_ENUM_ELMS ];

				} // Has enumeration values.

			} // Iterating data dictionary.

			return $list;															// ==>

		} // Get all enumerations.

		//
		// Convert variable name to index.
		//
		if( ! is_int( $theVariable ) )
		{
			$tmp = $this->parseName( $theVariable );
			if( $tmp === NULL )
				throw new InvalidArgumentException(
					"Unknown variable name [$theVariable]." );					// !@! ==>
			$theVariable = (int)$tmp;
		}

		//
		// Check variable index.
		//
		if( ($theVariable < 0)
			|| ($theVariable > $this->VariablesCount()) )
			throw new InvalidArgumentException(
				"Invalid variable index [$theVariable]." );						// !@! ==>

		//
		// Return current value.
		//
		if( $theValue === NULL )
		{
			//
			// Return enumeration name.
			//
			if( array_key_exists( self::kOFFSET_ENUM, $this->mDict[ $theVariable ] ) )
				return $this->mDict[ $theVariable ]
				[ self::kOFFSET_ENUM ]
				[ self::kOFFSET_ENUM_ELMS ];						// ==>

			return NULL;															// ==>

		} // Return current value.

		//
		// Check provided value.
		//
		if( ! is_array( $theValue ) )
			$theValue = [ $theValue ];

		//
		// Create enumeration record.
		//
		if( ! array_key_exists( self::kOFFSET_ENUM, $this->mDict[ $theVariable ] ) )
			$this->mDict[ $theVariable ][ self::kOFFSET_ENUM ]
				= [];

		return
			$this->mDict[ $theVariable ]
			[ self::kOFFSET_ENUM ]
			[ self::kOFFSET_ENUM_ELMS ]
				= $theValue;														// ==>

	} // VariableEnumVals.


	/*===================================================================================
	 *	Note																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage notes.</h4>
	 *
	 * This method can be used to append or retrieve notes, the method expects the following
	 * parameters:
	 *
	 * <ul>
	 * 	<li><b>$theNote</b>: Note string or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return notes related to next parameter.
	 * 		<li><tt>string</tt>: Append note related to next parameter.
	 * 	 </ul>
	 * 	<li><b>$theVariable</b>: Variable name:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Dataset notes.
	 * 		<li><tt>string</tt>: Variable name.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return an array of notes, or an empty array if there are no notes
	 * for the provided variable.
	 *
	 * @param string				$theNote			Note or operation.
	 * @param string				$theVariable		Variable name or <tt>NULL</tt>.
	 */
	public function Note( string $theNote = NULL, string $theVariable = NULL )
	{
		//
		// Init local storage.
		//
		$notes = [];
		$variable = ( $theVariable === NULL )
				  ? '_dta'
				  : $theVariable;

		//
		// Locate index.
		//
		$index = NULL;
		foreach( $this->mChars as $key => $value )
		{
			if( ($value[ self::kOFFSET_CHARS_VARNAME ] == $variable)
			 && ($value[ self::kOFFSET_CHARS_NAME ] == 'note0') )
			{
				$index = $key;
				break;															// =>
			}
		}

		//
		// Return note.
		//
		if( $theNote === NULL )
		{
			//
			// Handle no notes.
			//
			if( $index === NULL )
				return $notes;														// ==>

			//
			// Collect notes.
			//
			foreach( $this->mChars as $value )
			{
				if( ($value[ self::kOFFSET_CHARS_VARNAME ] == $variable)
				 && ($value[ self::kOFFSET_CHARS_NAME ] != 'note0') )
					$notes[ (int)substr( $value[ self::kOFFSET_CHARS_NAME ], 4 ) ]
						= $value[ self::kOFFSET_CHARS_DATA ];
			}

			//
			// Sort notes.
			//
			ksort( $notes );

			return array_values( $notes );											// ==>

		} // Return note.

		//
		// Create index.
		//
		if( $index === NULL )
		{
			$index = count( $this->mChars );
			$this->mChars[ $index ] = [
				self::kOFFSET_CHARS_VARNAME => $variable,
				self::kOFFSET_CHARS_NAME => 'note0',
				self::kOFFSET_CHARS_DATA => '0',
				self::kOFFSET_CHARS_SIZE => (129 * 2) + 1 + 1
			];

		} // New variable.

		//
		// Update index.
		//
		$count = (int)$this->mChars[ $index ][ self::kOFFSET_CHARS_DATA ];
		$this->mChars[ $index ][ self::kOFFSET_CHARS_DATA ] = (int)( $count + 1 );
		$this->mChars[ $index ][ self::kOFFSET_CHARS_SIZE ]
			= (129 * 2)
			+ mb_strlen( $this->mChars[ $index ][ self::kOFFSET_CHARS_DATA ], '8bit' )
			+ 1;

		//
		// Add note.
		//
		$note = [];
		$note[ self::kOFFSET_CHARS_VARNAME ] = $variable;
		$note[ self::kOFFSET_CHARS_NAME ]
			= "note" . $this->mChars[ $index ][ self::kOFFSET_CHARS_DATA ];
		$note[ self::kOFFSET_CHARS_DATA ] = $this->truncateString( $theNote, 67783 );
		$note[ self::kOFFSET_CHARS_SIZE ]
			= (129 * 2) + mb_strlen( $note[ self::kOFFSET_CHARS_DATA ], '8bit' );
		$this->mChars[] = $note;

		return $this->Note( NULL, $theVariable );									// ==>

	} // Note.



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
	 * This method can be used to read and parse the provided Stata file.
	 *
	 * In the process, the method will also set the current file to the provided value.
	 *
	 * @param string				$theFile			File path.
	 * @return SplFileObject		The file object.
	 *
	 * @uses Path()
	 * @uses headerInit()
	 * @uses mapInit()
	 * @uses dictInit()
	 * @uses headerRead()
	 * @uses typesRead()
	 * @uses namesRead()
	 */
	public function Read( string $theFile )
	{
		//
		// Clear object.
		//
		$this->headerInit();
		$this->mapInit();
		$this->dictInit();

		//
		// Set file.
		//
		$file = new SplFileObject( $this->Path( $theFile ), "r" );

		//
		// Read sections.
		//
		$this->headerRead( $file );
		$this->mapRead( $file );
		$this->typesRead( $file );
		$this->namesRead( $file );
		$this->sortRead( $file );
		$this->formatRead( $file );
		$this->valueLabelRead( $file );
		$this->labelRead( $file );
		$this->characteristicsRead( $file );

		return $file;																// ==>

	} // Read.


	/*===================================================================================
	 *	Write																			*
	 *==================================================================================*/

	/**
	 * <h4>Write the file.</h4>
	 *
	 * This method can be used to write the contents of the object to the provided Stata
	 * file.
	 *
	 * In the process, the method will also set the current file to the provided value.
	 *
	 * @param string				$theFile			File path.
	 * @return SplFileObject		The file object.
	 *
	 * @uses Path()
	 * @uses mapInit()
	 * @uses headerRead()
	 */
	public function Write( string $theFile )
	{
		//
		// Initialise object.
		//
		$this->mapInit();

		//
		// Set file.
		//
		$file = new SplFileObject( $this->Path( $theFile, TRUE ), "w+" );

		//
		// Write header.
		//
		$this->headerWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_MAP ] = $file->ftell();

		//
		// Write map.
		//
		$this->mapWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_TYPES ] = $file->ftell();

		//
		// Write types.
		//
		$this->typesWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_NAMES ] = $file->ftell();

		//
		// Write names.
		//
		$this->namesWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_SORT ] = $file->ftell();

		//
		// Write sort order.
		//
		$this->sortWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_FORMATS ] = $file->ftell();

		//
		// Write formats.
		//
		$this->formatWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_VALABNAMES ] = $file->ftell();

		//
		// Write value label names.
		//
		$this->valueLabelWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_VARLABEL ] = $file->ftell();

		//
		// Write labels.
		//
		$this->labelWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_CHARACTERISTICS ] = $file->ftell();

		//
		// Write characteristics.
		//
		$this->characteristicsWrite( $file );
		$this->mMap[ self::kTOKEN_DATASET_DATA ] = $file->ftell();

		return $file;																// ==>

	} // Write.



/*=======================================================================================
 *																						*
 *								PROTECTED HEADER INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	headerInit																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise the file header.</h4>
	 *
	 * This method can be used to initialise the header before adding data programmatically
	 * to the object, it will set the object's data members to their default values.
	 *
	 * @uses Path()
	 * @uses Format()
	 * @uses ByteOrder()
	 * @uses VariablesCount()
	 * @uses ObservationsCount()
	 * @uses DatasetLabel()
	 * @uses TimeStamp()
	 */
	protected function headerInit()
	{
		//
		// Reset members.
		//
		$this->Path( FALSE );
		$this->Format( '118' );
		$this->ByteOrder( 'MSF' );
		$this->VariablesCount( 0 );
		$this->ObservationsCount( 0 );
		$this->DatasetLabel( FALSE );
		$this->TimeStamp( "now" );

	} // headerInit.


	/*===================================================================================
	 *	headerRead																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the file header.</h4>
	 *
	 * This method can be used to read and parse the provided Stata file header.
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
	 *
	 * @uses Format()
	 * @uses ByteOrder()
	 * @uses VariablesCount()
	 * @uses ObservationsCount()
	 * @uses DatasetLabel()
	 * @uses TimeStamp()
	 * @uses readToken()
	 * @uses readUShort()
	 * @uses readUInt64()
	 * @uses readBString()
	 * @uses readTimeStamp()
	 */
	protected function headerRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_OPEN, FALSE );

		//
		// Get opening header token.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER, FALSE );

		//
		// Get release.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER_RELEASE, FALSE );
		$this->Format( $theFile->fread( 3 ) );
		$this->readToken( $theFile, self::kTOKEN_HEADER_RELEASE, TRUE );

		//
		// Get byte order.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER_ORDER, FALSE );
		$this->ByteOrder( $theFile->fread( 3 ) );
		$this->readToken( $theFile, self::kTOKEN_HEADER_ORDER, TRUE );

		//
		// Get variables count.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER_VARS, FALSE );
		$this->VariablesCount( $this->readUShort( $theFile ) );
		$this->readToken( $theFile, self::kTOKEN_HEADER_VARS, TRUE );

		//
		// Get observations count.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER_RECS, FALSE );
		$this->ObservationsCount( $this->readUInt64( $theFile ) );
		$this->readToken( $theFile, self::kTOKEN_HEADER_RECS, TRUE );

		//
		// Get dataset label.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER_LABEL, FALSE );
		$this->DatasetLabel( $this->readBString( $theFile ) );
		$this->readToken( $theFile, self::kTOKEN_HEADER_LABEL, TRUE );

		//
		// Get dataset time stamp.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER_STAMP, FALSE );
		$this->TimeStamp( $this->readTimeStamp( $theFile ) );
		$this->readToken( $theFile, self::kTOKEN_HEADER_STAMP, TRUE );

		//
		// Get closing header token.
		//
		$this->readToken( $theFile, self::kTOKEN_HEADER, TRUE );

	} // headerRead.


	/*===================================================================================
	 *	headerWrite																		*
	 *==================================================================================*/

	/**
	 * <h4>Write the file header.</h4>
	 *
	 * This method can be used to write the header to the current Stata file, it will return
	 * <tt>TRUE</tt> if the header was written and <tt>NULL</tt> if no file was declared;
	 * any error will raise an exception.
	 *
	 * <em>Note: we are writing fixed lengths for the following elements:
	 *
	 * <ul>
	 * 	<li><tt>release</tt>: 3.
	 * 	<li><tt>byteorder</tt>: 3.
	 * 	<li><tt>K</tt>: 4.
	 * 	<li><tt>N</tt>: 8.
	 * </ul></em>
	 *
	 * @param SplFileObject			$theFile			File to write.
	 * @return mixed				<tt>TRUE</tt> successful, <tt>NULL</tt> no file.
	 * @throws RuntimeException
	 *
	 * @uses Format()
	 * @uses ByteOrder()
	 * @uses VariablesCount()
	 * @uses ObservationsCount()
	 * @uses DatasetLabel()
	 * @uses TimeStamp()
	 * @uses readToken()
	 * @uses readUShort()
	 * @uses readUInt64()
	 * @uses readBString()
	 * @uses readTimeStamp()
	 */
	protected function headerWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_OPEN, FALSE );

		//
		// Write opening header token.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER, FALSE );

		//
		// Write release.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER_RELEASE, FALSE );
		$ok = $theFile->fwrite( $this->Format() );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write dataset format." );							// !@! ==>
		$this->writeToken( $theFile, self::kTOKEN_HEADER_RELEASE, TRUE );

		//
		// Write byte order.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER_ORDER, FALSE );
		$ok = $theFile->fwrite( $this->ByteOrder() );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write dataset byte order." );						// !@! ==>
		$this->writeToken( $theFile, self::kTOKEN_HEADER_ORDER, TRUE );

		//
		// Write variables count.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER_VARS, FALSE );
		$this->writeUShort( $theFile, $this->VariablesCount() );
		$this->writeToken( $theFile, self::kTOKEN_HEADER_VARS, TRUE );

		//
		// Write observations count.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER_RECS, FALSE );
		$this->writeUInt64( $theFile, $this->ObservationsCount() );
		$this->writeToken( $theFile, self::kTOKEN_HEADER_RECS, TRUE );

		//
		// Write dataset label.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER_LABEL, FALSE );
		$this->writeBString( $theFile, $this->DatasetLabel() );
		$this->writeToken( $theFile, self::kTOKEN_HEADER_LABEL, TRUE );

		//
		// Write dataset time stamp.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER_STAMP, FALSE );
		$this->writeTimeStamp( $theFile );
		$this->writeToken( $theFile, self::kTOKEN_HEADER_STAMP, TRUE );

		//
		// Write closing header token.
		//
		$this->writeToken( $theFile, self::kTOKEN_HEADER, TRUE );

	} // headerWrite.



/*=======================================================================================
 *																						*
 *								PROTECTED MAP INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	mapInit																			*
	 *==================================================================================*/

	/**
	 * <h4>Initialise the file map.</h4>
	 *
	 * This method can be used to initialise the map before writing to the file, all offsets
	 * will be set to zero.
	 */
	protected function mapInit()
	{
		//
		// Init map.
		//
		$this->mMap = [
			self::kTOKEN_DATASET_OPEN			 => 0,
			self::kTOKEN_DATASET_MAP			 => 0,
			self::kTOKEN_DATASET_TYPES			 => 0,
			self::kTOKEN_DATASET_NAMES			 => 0,
			self::kTOKEN_DATASET_SORT			 => 0,
			self::kTOKEN_DATASET_FORMATS		 => 0,
			self::kTOKEN_DATASET_VALABNAMES		 => 0,
			self::kTOKEN_DATASET_VARLABEL		 => 0,
			self::kTOKEN_DATASET_CHARACTERISTICS => 0,
			self::kTOKEN_DATASET_DATA			 => 0,
			self::kTOKEN_DATASET_LSTRING		 => 0,
			self::kTOKEN_DATASET_VALLABEL		 => 0,
			self::kTOKEN_CLOSE					 => 0,
			self::kTOKEN_EOF					 => 0
		];

	} // mapInit.


	/*===================================================================================
	 *	mapRead																			*
	 *==================================================================================*/

	/**
	 * <h4>Read the file map.</h4>
	 *
	 * This method can be used to read the file map from the provided file, the method
	 * expects the file pointer to be set on the map file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 */
	protected function mapRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_MAP, FALSE );

		//
		// Get file offsets.
		//
		foreach( array_keys( $this->mMap ) as $offset )
			$this->mMap[ $offset ]
				= $this->readUInt64( $theFile );

		//
		// Get closing token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_MAP, TRUE );

	} // mapRead.


	/*===================================================================================
	 *	mapWrite																		*
	 *==================================================================================*/

	/**
	 * <h4>Write the file map.</h4>
	 *
	 * This method can be used to write the file map into the provided file, the method
	 * expects the file pointer to be set on the map file token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function mapWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_MAP, FALSE );

		//
		// Write file offsets.
		//
		foreach( $this->mMap as $offset )
			$this->writeUInt64( $theFile, $offset );

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_MAP, TRUE );

	} // mapWrite.



/*=======================================================================================
 *																						*
 *							PROTECTED DICTIONARY INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	dictInit																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise the data dictionary.</h4>
	 *
	 * This method can be used to initialise the data dictionary.
	 */
	protected function dictInit()									{	$this->mDict = [];	}


	/*===================================================================================
	 *	typesRead																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the data types.</h4>
	 *
	 * This method can be used to read the data types from the provided file, the method
	 * expects the file pointer to be set on the types file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 */
	protected function typesRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_TYPES, FALSE );

		//
		// Get types.
		//
		$types = [];
		for( $i = 0; $i < $this->VariablesCount(); $i++ )
			$types[ $i ]
				= $this->readUShort( $theFile );

		//
		// Save types.
		//
		$this->VariableType( NULL, $types, FALSE );

		//
		// Get closing token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_TYPES, TRUE );

	} // typesRead.


	/*===================================================================================
	 *	typesWrite																		*
	 *==================================================================================*/

	/**
	 * <h4>Write the file map.</h4>
	 *
	 * This method can be used to write the file map into the provided file, the method
	 * expects the file pointer to be set on the map file token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function typesWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_TYPES, FALSE );

		//
		// Write types.
		//
		$list = $this->VariableType();
		foreach( $list as $element )
			$this->writeUShort( $theFile, $element );

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_TYPES, TRUE );

	} // typesWrite.


	/*===================================================================================
	 *	namesRead																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the variable names.</h4>
	 *
	 * This method can be used to read the variable names from the provided file, the method
	 * expects the file pointer to be set on the names file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 */
	protected function namesRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_NAMES, FALSE );

		//
		// Get names.
		//
		$list = [];
		$vars = $this->VariablesCount();
		while( $vars-- )
			$list[] = $this->readCString( $theFile, 129 );

		//
		// Save names.
		//
		$this->VariableName( NULL, $list);

		//
		// Get closing token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_NAMES, TRUE );

	} // namesRead.


	/*===================================================================================
	 *	namesWrite																		*
	 *==================================================================================*/

	/**
	 * <h4>Write the file map.</h4>
	 *
	 * This method can be used to write the variable names into the provided file, the
	 * method expects the file pointer to be set on the variable names token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function namesWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_NAMES, FALSE );

		//
		// Write names.
		//
		$list = $this->VariableName();
		foreach( $list as $element )
			$this->writeCString( $theFile, 129, $element );

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_NAMES, TRUE );

	} // namesWrite.


	/*===================================================================================
	 *	sortRead																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the sort order.</h4>
	 *
	 * This method can be used to read the sort order from the provided file, the method
	 * expects the file pointer to be set on the sort file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 */
	protected function sortRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_SORT, FALSE );

		//
		// Iterate sort order.
		//
		$order = 1;
		$count = $this->VariablesCount();
		while( $count-- )
		{
			//
			// Get order.
			//
			$variable = $this->readUShort( $theFile );
			if( $variable )
				$this->VariableSort( $variable - 1, $order++, FALSE );

			//
			// End of sort list.
			//
			else
				break;														// =>

		} // Scanning sort order.

		//
		// Read remaining elements.
		//
		while( $count-- )
			$this->readUShort( $theFile );

		//
		// Read closing element.
		//
		$this->readUShort( $theFile );

		//
		// Get closing token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_SORT, TRUE );

	} // sortRead.


	/*===================================================================================
	 *	sortWrite																		*
	 *==================================================================================*/

	/**
	 * <h4>Write the file map.</h4>
	 *
	 * This method can be used to write the variable names into the provided file, the
	 * method expects the file pointer to be set on the variable names token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function sortWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_SORT, FALSE );

		//
		// Get sort orders.
		//
		$list = $this->VariableSort();

		//
		// Order sort order.
		//
		asort( $list );

		//
		// Write order.
		//
		foreach( $list as $variable => $order )
			$this->writeUShort( $theFile, $variable + 1 );

		//
		// Write remaining and closing elements.
		//
		$count = count( $this->mDict ) - count( $list );
		while( $count-- )
			$this->writeUShort( $theFile, 0 );

		//
		// Close list.
		//
		$this->writeUShort( $theFile, 0 );

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_SORT, TRUE );

	} // sortWrite.


	/*===================================================================================
	 *	formatRead																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the data format.</h4>
	 *
	 * This method can be used to read the data format from the provided file, the method
	 * expects the file pointer to be set on the format file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 */
	protected function formatRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_FORMATS, FALSE );

		//
		// Iterate formats.
		//
		for( $variable = 0; $variable < $this->VariablesCount(); $variable++ )
			$this->VariableFormat( $variable, $this->readCString( $theFile, 57 ), FALSE );

		//
		// Get closing token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_FORMATS, TRUE );

	} // formatRead.


	/*===================================================================================
	 *	formatWrite																		*
	 *==================================================================================*/

	/**
	 * <h4>Write the data formats.</h4>
	 *
	 * This method can be used to write the data formats into the provided file, the
	 * method expects the file pointer to be set on the data formats token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function formatWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_FORMATS, FALSE );

		//
		// Iterate formats.
		//
		for( $variable = 0; $variable < $this->VariablesCount(); $variable++ )
			$this->writeCString(
				$theFile, 57, $this->mDict[ $variable ][ self::kOFFSET_FORMAT ]
			);

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_FORMATS, TRUE );

	} // formatWrite.


	/*===================================================================================
	 *	valueLabelRead																	*
	 *==================================================================================*/

	/**
	 * <h4>Read the variable value labels.</h4>
	 *
	 * This method can be used to read the variable value labels from the provided file, the
	 * method expects the file pointer to be set on the value labels file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 */
	protected function valueLabelRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_VALABNAMES, FALSE );

		//
		// Iterate labels.
		//
		for( $variable = 0; $variable < $this->VariablesCount(); $variable++ )
		{
			$label = $this->readCString( $theFile, 129 );
			if( strlen( $label ) )
				$this->VariableEnumName( $variable, $label, FALSE );
		}

		//
		// Get closing token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_VALABNAMES, TRUE );

	} // valueLabelRead.


	/*===================================================================================
	 *	valueLabelWrite																	*
	 *==================================================================================*/

	/**
	 * <h4>Write the variable value labels.</h4>
	 *
	 * This method can be used to write the variable value labels into the provided file,
	 * the method expects the file pointer to be set on the labels token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function valueLabelWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_VALABNAMES, FALSE );

		//
		// Iterate labels.
		//
		for( $variable = 0; $variable < $this->VariablesCount(); $variable++ )
			$this->writeCString(
				$theFile, 129, (string)$this->VariableEnumName( $variable )
			);

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_VALABNAMES, TRUE );

	} // valueLabelWrite.


	/*===================================================================================
	 *	labelRead																		*
	 *==================================================================================*/

	/**
	 * <h4>Read the variable labels.</h4>
	 *
	 * This method can be used to read the variable labels from the provided file, the
	 * method expects the file pointer to be set on the labels file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 */
	protected function labelRead( SplFileObject $theFile )
	{
		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_VARLABEL, FALSE );

		//
		// Iterate labels.
		//
		for( $variable = 0; $variable < $this->VariablesCount(); $variable++ )
			$this->VariableLabel( $variable, $this->readCString( $theFile, 321 ), FALSE );

		//
		// Get closing token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_VARLABEL, TRUE );

	} // labelRead.


	/*===================================================================================
	 *	labelWrite																		*
	 *==================================================================================*/

	/**
	 * <h4>Write the variable labels.</h4>
	 *
	 * This method can be used to write the variable labels into the provided file, the
	 * method expects the file pointer to be set on the labels token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function labelWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_VARLABEL, FALSE );

		//
		// Iterate labels.
		//
		for( $variable = 0; $variable < $this->VariablesCount(); $variable++ )
			$this->writeCString( $theFile, 321, $this->VariableLabel( $variable ) );

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_VARLABEL, TRUE );

	} // labelWrite.


	/*===================================================================================
	 *	characteristicsRead																*
	 *==================================================================================*/

	/**
	 * <h4>Read the characteristics.</h4>
	 *
	 * This method can be used to read the characteristics from the provided file, the
	 * method expects the file pointer to be set on the characteristics file token.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @throws RuntimeException
	 */
	protected function characteristicsRead( SplFileObject $theFile )
	{
		//
		// Init characteristics.
		//
		$this->mChars = [];

		//
		// Get opening token.
		//
		$this->readToken( $theFile, self::kTOKEN_DATASET_CHARACTERISTICS, FALSE );

		//
		// Iterate characteristics.
		//
		while( TRUE )
		{
			//
			// Check if there is a characteristics.
			//
			$tmp = $theFile->fread( 4 );
			if( $tmp == '<ch>' )
			{
				//
				// Add element.
				//
				$index = count( $this->mChars );
				$this->mChars[ $index ] = [];
				$element = & $this->mChars[ $index ];

				//
				// Read block size.
				//
				$element[ self::kOFFSET_CHARS_SIZE ]
					= $this->readUInt32( $theFile );

				//
				// Read variable name.
				//
				$element[ self::kOFFSET_CHARS_VARNAME ]
					= $this->readCString( $theFile, 129 );

				//
				// Read characteristic name.
				//
				$element[ self::kOFFSET_CHARS_NAME ]
					= $this->readCString( $theFile, 129 );

				//
				// Read characteristic data.
				//
				$element[ self::kOFFSET_CHARS_DATA ]
					= $this->readCString(
						$theFile, $element[ self::kOFFSET_CHARS_SIZE ] - (129 * 2)
					);

				//
				// Read end of element.
				//
				$tmp = $theFile->fread( 5 );
				if( $tmp == '</ch>' )
					continue;													// =>

				throw new RuntimeException(
					"Unable to read end of characteristics element block " .
					"[$tmp]." );												// !@! ==>

			} // Found characteristic.

			//
			// Handle end of block.
			//
			elseif( $tmp == '</ch' )
			{
				//
				// Init local storage.
				//
				$token = 'aracteristics>';

				//
				// Try to read rest of closing block.
				//
				$tmp = $theFile->fread( strlen( $token ) );
				if( $tmp != $token )
					throw new RuntimeException(
						"Unable to read end of characteristics block " .
						"[$tmp]." );											// !@! ==>

				//
				// Exit loop.
				//
				break;															// =>

			} // End of block.

			//
			// Handle error.
			//
			else
				throw new RuntimeException(
					"Unexpected end of characteristics block [$tmp]." );		// !@! ==>

		} // Iterating characteristics.

	} // characteristicsRead.


	/*===================================================================================
	 *	characteristicsWrite															*
	 *==================================================================================*/

	/**
	 * <h4>Write the characteristics.</h4>
	 *
	 * This method can be used to write the characteristics into the provided file, the
	 * method expects the file pointer to be set on the characteristics token.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 */
	protected function characteristicsWrite( SplFileObject $theFile )
	{
		//
		// Write opening token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_CHARACTERISTICS, FALSE );

		//
		// Iterate characteristics.
		//
		foreach( $this->mChars as $char )
		{
			//
			// Open element.
			//
			$this->writeToken( $theFile, self::kTOKEN_DATASET_CHARACTERISTICS_ELM, FALSE );

			//
			// Write element size.
			//
			$this->writeUInt32( $theFile, $char[ self::kOFFSET_CHARS_SIZE ] );

			//
			// Write variable name.
			//
			$this->writeCString( $theFile, 129, $char[ self::kOFFSET_CHARS_VARNAME ] );

			//
			// Write characteristic name.
			//
			$this->writeCString( $theFile, 129, $char[ self::kOFFSET_CHARS_NAME ] );

			//
			// Write characteristic data.
			//
			$this->writeCString(
				$theFile,
				mb_strlen( $char[ self::kOFFSET_CHARS_DATA ] ) + 1,
				$char[ self::kOFFSET_CHARS_DATA ] );

			//
			// Close element.
			//
			$this->writeToken( $theFile, self::kTOKEN_DATASET_CHARACTERISTICS_ELM, TRUE );

		} // Iterating characteristics.

		//
		// Write closing token.
		//
		$this->writeToken( $theFile, self::kTOKEN_DATASET_CHARACTERISTICS, TRUE );

	} // characteristicsWrite.



/*=======================================================================================
 *																						*
 *								PROTECTED READING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	readToken																		*
	 *==================================================================================*/

	/**
	 * <h4>Read a file token.</h4>
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
	 */
	protected function readToken( SplFileObject $theFile,
								  string		 $theToken,
								  bool			 $doClose = FALSE )
	{
		//
		// Set token.
		//
		$token = ( ! $doClose )
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
			throw new RuntimeException(
				"Invalid file token [$tmp], expecting [$token]." );				// !@! ==>

	} // readToken.


	/*===================================================================================
	 *	writeToken																		*
	 *==================================================================================*/

	/**
	 * <h4>Write a file token.</h4>
	 *
	 * This method can be used to write a token.
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
	 * @param SplFileObject			$theFile			File to write.
	 * @param string				$theToken			Token to read.
	 * @param bool					$doClose			<tt>TRUE</tt> closing tag.
	 * @throws RuntimeException
	 */
	protected function writeToken( SplFileObject $theFile,
								   string		 $theToken,
								   bool			 $doClose = FALSE )
	{
		//
		// Set token.
		//
		$token = ( ! $doClose )
			? ('<' . $theToken . '>')
			: ('</' . $theToken . '>');

		//
		// Write token.
		//
		$ok = $theFile->fwrite( $token );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write file token [$token]." );						// !@! ==>

	} // writeToken.


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
	 *
	 * @uses ByteOrder()
	 */
	protected function readUShort( SplFileObject $theFile )
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
				return unpack( 'n', $data )[ 1 ];									// ==>

			case 'LSF':
				return unpack( 'v', $data )[ 1 ];									// ==>
		}

		throw new RuntimeException(
			"Invalid byte order [$tmp]." );										// !@! ==>

	} // readUShort.


	/*===================================================================================
	 *	writeUShort																		*
	 *==================================================================================*/

	/**
	 * <h4>Write unsigned short.</h4>
	 *
	 * This method can be used to write an unsigned short.
	 *
	 * @param SplFileObject			$theFile			File to write.
	 * @param int					$theValue			Value to write.
	 * @throws RuntimeException
	 *
	 * @uses ByteOrder()
	 */
	protected function writeUShort( SplFileObject $theFile, int $theValue )
	{
		//
		// Pack value.
		//
		switch( $tmp = $this->ByteOrder() )
		{
			case 'MSF':
				$value = pack( 'n', $theValue );
				break;

			case 'LSF':
				$value = pack( 'v', $theValue );
				break;
			
			default:
				throw new RuntimeException(
					"Invalid byte order [$tmp]." );								// !@! ==>
		}

		//
		// write value.
		//
		$ok = $theFile->fwrite( $value );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write unsigned short." );							// !@! ==>

	} // writeUShort.


	/*===================================================================================
	 *	readUInt32																		*
	 *==================================================================================*/

	/**
	 * <h4>Read unsigned long.</h4>
	 *
	 * This method can be used to read a 32 bit unsigned long.
	 *
	 * <em>Note that the method returns a signed integer, since PHP does not handle unsigned
	 * integers.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return int					The signed integer.
	 * @throws RuntimeException
	 *
	 * @uses ByteOrder()
	 */
	protected function readUInt32( SplFileObject $theFile )
	{
		//
		// Read value.
		//
		$data = $theFile->fread( 4 );
		if( $data === FALSE )
			throw new RuntimeException(
				"Unable to read unsigned long." );								// !@! ==>

		//
		// Unpack.
		//
		switch( $tmp = $this->ByteOrder() )
		{
			case 'MSF':
				return unpack( 'N', $data )[ 1 ];									// ==>

			case 'LSF':
				return unpack( 'V', $data )[ 1 ];									// ==>
		}

		throw new RuntimeException(
			"Invalid byte order [$tmp]." );										// !@! ==>

	} // readUInt32.


	/*===================================================================================
	 *	writeUInt32																		*
	 *==================================================================================*/

	/**
	 * <h4>Write unsigned long.</h4>
	 *
	 * This method can be used to write a 32 bit unsigned long.
	 *
	 * <em>Note that the method expects a signed integer, since PHP does not handle unsigned
	 * integers.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @param int					$theValue			Value to write.
	 * @throws RuntimeException
	 *
	 * @uses ByteOrder()
	 */
	protected function writeUInt32( SplFileObject $theFile, int $theValue )
	{
		//
		// Pack value.
		//
		switch( $tmp = $this->ByteOrder() )
		{
			case 'MSF':
				$value = pack( 'N', $theValue );
				break;

			case 'LSF':
				$value = pack( 'V', $theValue );
				break;

			default:
				throw new RuntimeException(
					"Invalid byte order [$tmp]." );								// !@! ==>
		}

		//
		// write value.
		//
		$ok = $theFile->fwrite( $value );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write unsigned long." );								// !@! ==>

	} // writeUInt32.


	/*===================================================================================
	 *	readUInt64																		*
	 *==================================================================================*/

	/**
	 * <h4>Read unsigned long.</h4>
	 *
	 * This method can be used to read a 64 bit unsigned long.
	 *
	 * <em>Note that the method returns a signed integer, since PHP does not handle unsigned
	 * integers.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return int					The signed integer.
	 * @throws RuntimeException
	 *
	 * @uses ByteOrder()
	 */
	protected function readUInt64( SplFileObject $theFile )
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
				return unpack( 'J', $data )[ 1 ];									// ==>

			case 'LSF':
				return unpack( 'P', $data )[ 1 ];									// ==>
		}

		throw new RuntimeException(
			"Invalid byte order [$tmp]." );										// !@! ==>

	} // readUInt64.


	/*===================================================================================
	 *	writeUInt64																		*
	 *==================================================================================*/

	/**
	 * <h4>Write unsigned long.</h4>
	 *
	 * This method can be used to write a 64 bit unsigned long.
	 *
	 * <em>Note that the method expects a signed integer, since PHP does not handle unsigned
	 * integers.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @param int					$theValue			Value to write.
	 * @throws RuntimeException
	 *
	 * @uses ByteOrder()
	 */
	protected function writeUInt64( SplFileObject $theFile, int $theValue )
	{
		//
		// Pack value.
		//
		switch( $tmp = $this->ByteOrder() )
		{
			case 'MSF':
				$value = pack( 'J', $theValue );
				break;

			case 'LSF':
				$value = pack( 'P', $theValue );
				break;

			default:
				throw new RuntimeException(
					"Invalid byte order [$tmp]." );								// !@! ==>
		}

		//
		// write value.
		//
		$ok = $theFile->fwrite( $value );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write unsigned long." );								// !@! ==>

	} // writeUInt64.


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
	 *
	 * @uses readUShort()
	 */
	protected function readBString( SplFileObject $theFile )
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
	 *	writeBString																	*
	 *==================================================================================*/

	/**
	 * <h4>Write byte string.</h4>
	 *
	 * This method can be used to write a string prefixed by a length byte.
	 *
	 * The lenght value contains the number of byte in the string, while the string is an
	 * UTF-8 string, so a character may be made up of at most 4 bytes.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @param string				$theValue			Value to write.
	 * @return mixed				The string or <tt>FALSE</tt>.
	 * @throws RuntimeException
	 *
	 * @uses readUShort()
	 */
	protected function writeBString( SplFileObject $theFile, string $theValue )
	{
		//
		// Get and write length.
		//
		$this->writeUShort( $theFile, mb_strlen( $theValue, '8bit' ) );

		//
		// Write string.
		//
		$ok = $theFile->fwrite( $theValue );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write string [$theValue]." );						// !@! ==>

	} // writeBString.


	/*===================================================================================
	 *	readCString																		*
	 *==================================================================================*/

	/**
	 * <h4>Read padded C-string.</h4>
	 *
	 * This method can be used to read a C-string contained in a fixed length buffer.
	 *
	 * The length parameter is expressed as the field length in bytes, the resulting string
	 * will be truncated at the first encountered zero binary character, or at the end of
	 * the field.
	 *
	 * If you provide a zero length, the method will return <tt>NULL</tt>.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @param int					$theLength			Field size in bytes.
	 * @return string				The string.
	 * @throws RuntimeException
	 *
	 * @uses readUShort()
	 */
	protected function readCString( SplFileObject $theFile, int $theLength )
	{
		//
		// Read length.
		//
		if( $theLength )
		{
			//
			// Read field.
			//
			$string = $theFile->fread( $theLength );
			if( $string === FALSE )
				throw new RuntimeException(
					"Unable to read [$theLength] bytes." );						// !@! ==>

			return mb_strstr( $string, "\0", TRUE, 'UTF-8' );						// ==>

		} // Has length.

		return NULL;																// ==>

	} // readCString.


	/*===================================================================================
	 *	writeCString																	*
	 *==================================================================================*/

	/**
	 * <h4>Write padded C-string.</h4>
	 *
	 * This method can be used to write a zero padded string.
	 *
	 * The length parameter is expressed as the field length in bytes, the method will write
	 * binary zero characters until the full field has been padded.
	 *
	 * The string is expected to be an UTF-8 string, which means that each character may be
	 * at most 4 bytes long: the method will truncate the string to fit the provided field
	 * size.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @param int					$theLength			Field size in bytes.
	 * @param string				$theValue			Value to write.
	 * @return mixed				The string or <tt>FALSE</tt>.
	 * @throws RuntimeException
	 *
	 * @uses readUShort()
	 */
	protected function writeCString( SplFileObject $theFile,
									 int		   $theLength,
									 string		   $theValue )
	{
		//
		// Truncate string.
		//
		$theValue = $this->truncateString( $theValue, $theLength );

		//
		// Write string.
		//
		$ok = $theFile->fwrite( $theValue );
		if( $ok === NULL )
			throw new RuntimeException(
				"Unable to write string [$theValue]." );						// !@! ==>

		//
		// Add padding.
		//
		$length = $theLength - mb_strlen( $theValue, '8bit' );
		while( $length-- )
		{
			//
			// Add padding.
			//
			$ok = $theFile->fwrite( "\0" );
			if( $ok === NULL )
				throw new RuntimeException(
					"Unable to write string padding." );						// !@! ==>

		} // Needs padding.

	} // writeCString.


	/*===================================================================================
	 *	readTimeStamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Read time stamp.</h4>
	 *
	 * This method can be used to read the dataset time stamp.
	 *
	 * If there is no time stamp, the method will return <tt>FALSE</tt>.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @return mixed				The time stamp string or <tt>FALSE</tt>.
	 * @throws RuntimeException
	 */
	protected function readTimeStamp( SplFileObject $theFile )
	{
		//
		// Read type.
		//
		$type = $theFile->fread( 1 );
		if( $type === FALSE )
			throw new RuntimeException(
				"Unable to read byte." );										// !@! ==>
		$type = unpack( 'C', $type )[ 1 ];

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


	/*===================================================================================
	 *	writeTimeStamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Write time stamp.</h4>
	 *
	 * This method can be used to write the dataset time stamp.
	 *
	 * @param SplFileObject			$theFile			File to parse.
	 * @throws RuntimeException
	 */
	protected function writeTimeStamp( SplFileObject $theFile )
	{
		//
		// Get time stamp.
		//
		$stamp = $this->TimeStamp();
		if( $stamp instanceof DateTime )
		{
			//
			// Write type.
			//
			$ok = $theFile->fwrite( pack( 'C', 17 ) );
			if( $ok === NULL )
				throw new RuntimeException(
					"Unable to write time stamp type." );						// !@! ==>

			//
			// Write time stamp.
			//
			$ok = $theFile->fwrite( $stamp->format( 'd M Y H:i' ) );
			if( $ok === NULL )
				throw new RuntimeException(
					"Unable to write time stamp." );							// !@! ==>

		} // Has time stamp.

		//
		// No time stamp.
		//
		else
		{
			$ok = $theFile->fwrite( 0x00 );
			if( $ok === NULL )
				throw new RuntimeException(
					"Unable to write empty time stamp." );						// !@! ==>

		} // No time stamp.

	} // writeTimeStamp.



/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	parseType																		*
	 *==================================================================================*/

	/**
	 * <h4>Parse a data type.</h4>
	 *
	 * This method can be used to convert a data type from and to code and name.
	 *
	 * The method expects a single parameter that can be either an integer representing a
	 * data type code as stored in the Stata file, or a string representing the type name.
	 *
	 * When provided a string, the method will return the code, when provided an integer,
	 * te method will return the name.
	 *
	 * If the provided value is not valid, the method will raise an exception.
	 *
	 * If you provide an array, the method will return an array of parsed elements.
	 *
	 * @param mixed					$theValue			Type name, code or list.
	 * @return mixed				Type name or code.
	 * @throws InvalidArgumentException
	 */
	protected function parseType( $theValue )
	{
		//
		// Handle list.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate list.
			//
			$list = [];
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->parseType( $value );

			return $list;															// ==>

		} // Provided list.

		//
		// Handle code.
		//
		if( is_integer( $theValue ) )
		{
			//
			// Check value.
			//
			if( $theValue <= 0 )
				throw new InvalidArgumentException(
					"Invalid type [$theValue]: code should be positive." );		// !@! ==>

			//
			// Handle string.
			//
			if( $theValue <= 2045 )
				return "str$theValue";												// ==>

			//
			// Parse code.
			//
			switch( $theValue )
			{
				case 32768:
					return "strL";													// ==>

				case 65526:
					return "double";												// ==>

				case 65527:
					return "float";													// ==>

				case 65528:
					return "long";													// ==>

				case 65529:
					return "int";													// ==>

				case 65530:
					return "byte";													// ==>

				default:
					throw new InvalidArgumentException(
						"Invalid type [$theValue]." );							// !@! ==>
			}

		} // Provided code.

		//
		// Handle string.
		//
		$theValue = trim( $theValue );
		if( substr( $theValue, 0, 3 ) == 'str' )
		{
			//
			// Handle string type.
			//
			$type = substr( $theValue, 3 );

			//
			// Handle long string.
			//
			if( $type == 'L' )
				return 32768;														// ==>

			//
			// Handle fixed string.
			//
			elseif( is_numeric( $type ) )
			{
				//
				// Check type.
				//
				$type = (int)$type;
				if( $type > 2045 )
					throw new InvalidArgumentException(
						"Invalid type [$theValue]" );							// !@! ==>

				return $type;														// ==>
			}

			//
			// Handle errors.
			//
			else
				throw new InvalidArgumentException(
					"Invalid type [$theValue]" );								// !@! ==>

		} // Is string.

		//
		// Handle other types.
		//
		switch( $theValue )
		{
			case 'double':
				return 65526;														// ==>

			case 'float':
				return 65527;														// ==>

			case 'long':
				return 65528;														// ==>

			case 'int':
				return 65529;														// ==>

			case 'byte':
				return 65526;														// ==>

			case 'double':
				return 65530;														// ==>

			default:
				throw new InvalidArgumentException(
					"Invalid type [$theValue]." );						// !@! ==>
		}

	} // parseType.


	/*===================================================================================
	 *	parseName																		*
	 *==================================================================================*/

	/**
	 * <h4>Parse a variable name.</h4>
	 *
	 * This method can be used to convert a variable name into the variable index and back.
	 *
	 * The method expects a single parameter that represents the variable name if string, or
	 * the variable index if integer: the method will return the corresponding opposite.
	 *
	 * If the provided name does not match any existing variable, or the provided variable
	 * index is out of bounds, the method will return <tt>NULL</tt>.
	 *
	 * If you provide an array, the method will return an array of parsed elements.
	 *
	 * @param mixed					$theValue			Variable name, index or list.
	 * @return mixed				Variable index or <tt>NULL</tt>.
	 */
	protected function parseName( $theValue )
	{
		//
		// Handle list.
		//
		if( is_array( $theValue ) )
		{
			//
			// Iterate list.
			//
			$list = [];
			foreach( $theValue as $key => $value )
				$list[ $key ]
					= $this->parseName( $value );

			return $list;															// ==>

		} // Provided list.

		//
		// Handle index.
		//
		if( is_int( $theValue ) )
		{
			//
			// Check index.
			//
			if( array_key_exists( $theValue, $this->mDict ) )
				return $this->mDict[ $theValue ][ self::kOFFSET_NAME ];				// ==>

			return NULL;															// ==>

		} // Provided variable index.

		//
		// Iterate data dictionary.
		//
		foreach( $this->mDict as $key => $value )
		{
			//
			// Match name.
			//
			if( $value[ self::kOFFSET_NAME ] == (string)$theValue )
				return $key;														// ==>
		}

		return NULL;																// ==>

	} // parseName.


	/*===================================================================================
	 *	truncateString																	*
	 *==================================================================================*/

	/**
	 * <h4>Truncate a string.</h4>
	 *
	 * This method can be used to truncate a UTF-8 string to a maximum byte length, the
	 * method expects the string and the maximum length in bytes and will return the
	 * truncated string.
	 *
	 * @param string				$theString			String.
	 * @param int					$theLength			Maximum length in bytes.
	 * @return string				Truncated string.
	 */
	protected function truncateString( string $theString, int $theLength )
	{
		//
		// Truncate string.
		//
		$chars = mb_strlen( $theString, 'UTF-8' );
		while( mb_strlen( $theString, '8bit' ) > $theLength )
			$theString = mb_substr( $theString, 0, --$chars, 'UTF-8' );

		return $theString;															// ==>

	} // truncateString.


	/*===================================================================================
	 *	addCharacteristic																*
	 *==================================================================================*/

	/**
	 * <h4>Add a characteristics record.</h4>
	 *
	 * This method can be used to append a characteristics record, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theVariable</b>: Variable name.
	 * 	<li><b>$theName</b>: Characteristics name.
	 * 	<li><b>$theData</b>: Characteristics data.
	 * 	<li><b>$theSize</b>: Record size, or <tt>NULL</tt> to calculate.
	 * </ul>
	 *
	 * @param string				$theVariable		Variable name.
	 * @param string				$theName			Characteristics name.
	 * @param string				$theData			Characteristics data.
	 * @param int					$theSize			Characteristics size.
	 */
	protected function addCharacteristic( string $theVariable,
										  string $theName,
										  string $theData,
										  int	 $theSize = NULL )
	{
		//
		// Init local storage.
		//
		$record = [];

		//
		// Set names.
		//
		$record[ self::kOFFSET_CHARS_VARNAME ] = $this->truncateString( $theVariable, 128 );
		$record[ self::kOFFSET_CHARS_NAME ] = $this->truncateString( $theName, 128 );

		//
		// Set data.
		//
		$record[ self::kOFFSET_CHARS_DATA ]
			= $this->truncateString( $theData, 67784 - (129 * 2) - 1 );

		//
		// Set length.
		//
		if( $theSize === NULL )
			$theSize = (129 * 2) + mb_strlen( $theData, '8bit' ) + 1;
		$record[ self::kOFFSET_CHARS_SIZE ] = $theSize;

		//
		// Add record.
		//
		$this->mChars[] = $record;

	} // addCharacteristic.




} // class StataFile.


?>
