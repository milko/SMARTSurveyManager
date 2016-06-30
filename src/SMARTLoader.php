<?php

/**
 * SMARTLoader.php
 *
 * This file contains the definition of the {@link SMARTLoader} class.
 */

/*=======================================================================================
 *																						*
 *									SMARTLoader.php										*
 *																						*
 *======================================================================================*/

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Collection;

/**
 * <h4>SMART Survey Loader.</h4>
 *
 * This class extends handles household, mother and child SMART surveys and aggregates them
 * into a single dataset.
 *
 * The class will create a <tt>household</tt>, <tt>mother</tt> and <tt>child</tt> set of
 * collections, normalise their value types, signal eventual duplicates and finally merge
 * the three datasets into a single one.
 *
 * The class is initialised by providing details on the datasets, such as the file path,
 * the labels and data rows, the administrative unit, team, cluster and unit identifier
 * columns, then operations can be performed in this order:
 *
 * <ul>
 * 	<li>Load dataset. This operation will load the data from the survey file into the
 * 		related collection, casting variables to the correct type.
 * 	<li>Check dataset. This operation will verify whether the dataset contains duplicate
 * 		entries, in which case a column will be added to the collection documents
 * 		identifying the duplicates group.
 * 	<li>Aggregate datasets. This operation will merge the three datasets into a single one
 * 		in which the child is the common denominator and the mother and household data will
 * 		be appended to each child document.
 * </ul>
 *
 * This class handles datasets in the <em>Excel</em> format and uses <em>MongoDB</em> as the
 * database engine.
 *
 *	@package	SMART
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/06/2016
 */
class SMARTLoader
{
	/**
	 * <h4>Default client DSN.</h4>
	 *
	 * This constant holds the <em>default client connection data source name</em>.
	 *
	 * @var string
	 */
	const kNAME_DSN = 'mongodb://localhost:27017';

	/**
	 * <h4>Default database name.</h4>
	 *
	 * This constant holds the <em>default database name</em>.
	 *
	 * @var string
	 */
	const kNAME_DATABASE = 'SMART';

	/**
	 * <h4>Default survey collection name.</h4>
	 *
	 * This constant holds the <em>default survey collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_SURVEY = 'survey';

	/**
	 * <h4>Default household collection name.</h4>
	 *
	 * This constant holds the <em>default household collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_HOUSEHOLD = 'household';

	/**
	 * <h4>Default mother collection name.</h4>
	 *
	 * This constant holds the <em>default mother collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_MOTHER = 'mother';

	/**
	 * <h4>Default child collection name.</h4>
	 *
	 * This constant holds the <em>default child collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_CHILD = 'child';

	/**
	 * <h4>Default data dictionary collection name.</h4>
	 *
	 * This constant holds the <em>default data dictionary collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_DDICT = 'ddict';

	/**
	 * <h4>Original dataset collection name prefix.</h4>
	 *
	 * This constant holds the <em>name prefix</em> for the <em>original dataset
	 * collection</em>.
	 *
	 * @var string
	 */
	const kNAME_PREFIX_ORIGINAL = 'original_';

	/**
	 * <h4>Dataset file path.</h4>
	 *
	 * This constant holds the <em>offset</em> that <em>identifies the dataset file
	 * path</em>.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_FILE = 'DATASET_PATH';

	/**
	 * <h4>Dataset header line.</h4>
	 *
	 * This constant holds the <em>offset</em> that <em>contains the dataset header line
	 * number</em>.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_HEADER = 'DATASET_HEADER_LINE';

	/**
	 * <h4>Dataset data line.</h4>
	 *
	 * This constant holds the <em>offset</em> that <em>contains the dataset data line
	 * number</em>.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_DATA = 'DATASET_DATA_LINE';

	/**
	 * <h4>Dataset survey date variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the survey date
	 * variable</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_DATE = 'DATASET_DATE_VARIABLE';

	/**
	 * <h4>Dataset location variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the location number</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_LOCATION = 'DATASET_LOCATION_VARIABLE';

	/**
	 * <h4>Dataset team variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the survey team number</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_TEAM = 'DATASET_TEAM_VARIABLE';

	/**
	 * <h4>Dataset cluster variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the cluster number</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_CLUSTER = 'DATASET_CLUSTER_VARIABLE';

	/**
	 * <h4>Dataset identifier variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the unit identifier number</em> in the dataset, this for children it would be
	 * the child number, for mothers the mother number and for households the household
	 * number.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_IDENTIFIER = 'DATASET_IDENTIFIER_VARIABLE';

	/**
	 * <h4>Dataset household identifier variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the household number</em> in the dataset, this variable corresponds to the
	 * household number in mother and child datasets.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_HOUSEHOLD = 'DATASET_HOUSEHOLD_VARIABLE';

	/**
	 * <h4>Dataset mother identifier variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the mother number</em> in the dataset, this variable corresponds to the
	 * mother number in child datasets.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_MOTHER = 'DATASET_MOTHER_VARIABLE';

	/**
	 * <h4>Survey date offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>survey date</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_DATE = '@SURVEY_DATE';

	/**
	 * <h4>Survey location offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>survey location</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_LOCATION = '@SURVEY_LOCATION';

	/**
	 * <h4>Team number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>team number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_TEAM = '@SURVEY_TEAM';

	/**
	 * <h4>Cluster number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>cluster number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_CLUSTER = '@SURVEY_CLUSTER';

	/**
	 * <h4>Household number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>household number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_HOUSEHOLD = '@SURVEY_HOUSEHOLD';

	/**
	 * <h4>Mother number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>mother number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_MOTHER = '@SURVEY_MOTHER';

	/**
	 * <h4>Unit identifier offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>identifier number</em> in
	 * collections, this corresponds to the child, mother and household numbers in their
	 * respective datasets.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_IDENTIFIER = '@SURVEY_UNIT';

	/**
	 * <h4>Household reference.</h4>
	 *
	 * This constant holds the <em>variable name</em> for the <em>household unique ID</em>.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_HOUSEHOLD_ID = '@SURVEY_HOUSEHOLD_ID';

	/**
	 * <h4>Mother reference.</h4>
	 *
	 * This constant holds the <em>variable name</em> for the <em>mother unique ID</em>.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_MOTHER_ID = '@SURVEY_MOTHER_ID';

	/**
	 * <h4>Children count.</h4>
	 *
	 * This constant holds the <em>variable name</em> for the <em>children count</em>.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_CHILD_COUNT = '@CHILD_COUNT';

	/**
	 * <h4>Mothers count.</h4>
	 *
	 * This constant holds the <em>variable name</em> for the <em>mothers count</em>.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_MOTHER_COUNT = '@MOTHER_COUNT';

	/**
	 * <h4>Child dataset selector.</h4>
	 *
	 * This constant holds the <em>child dataset selector</em>.
	 *
	 * @var int
	 */
	const kDATASET_SELECTOR_CHILD = 0x00000001;

	/**
	 * <h4>Mother dataset selector.</h4>
	 *
	 * This constant holds the <em>mother dataset selector</em>.
	 *
	 * @var int
	 */
	const kDATASET_SELECTOR_MOTHER = 0x00000002;

	/**
	 * <h4>Household dataset selector.</h4>
	 *
	 * This constant holds the <em>household dataset selector</em>.
	 *
	 * @var int
	 */
	const kDATASET_SELECTOR_HOUSEHOLD = 0x00000004;

	/**
	 * <h4>Merged dataset selector.</h4>
	 *
	 * This constant holds the <em>merged dataset selector</em>.
	 *
	 * @var int
	 */
	const kDATASET_SELECTOR_MERGED = 0x00000008;

	/**
	 * <h4>Data dictionary child identifier.</h4>
	 *
	 * This constant holds the <em>unique identifier</em> of the <em>child data dictionary
	 * record</em>.
	 *
	 * @var string
	 */
	const kDDICT_CHILD_ID = 'CHILD';

	/**
	 * <h4>Data dictionary mother identifier.</h4>
	 *
	 * This constant holds the <em>unique identifier</em> of the <em>mother data dictionary
	 * record</em>.
	 *
	 * @var string
	 */
	const kDDICT_MOTHER_ID = 'MOTHER';

	/**
	 * <h4>Data dictionary household identifier.</h4>
	 *
	 * This constant holds the <em>unique identifier</em> of the <em>household data
	 * dictionary record</em>.
	 *
	 * @var string
	 */
	const kDDICT_HOUSEHOLD_ID = 'HOUSEHOLD';

	/**
	 * <h4>Data dictionary status.</h4>
	 *
	 * This constant holds the <em>dataset status</em> which indicates whether the dataset
	 * was loaded, processed or if it has errors:
	 *
	 * <ul>
	 * 	<li><tt>{@link kSTATUS_IDLE}</tt>: Idle, the dataset has not yet beendeclared.
	 * 	<li><tt>{@link kSTATUS_LOADED}</tt>: Loaded, the dataset has been loaded in the
	 * 		database.
	 * 	<li><tt>{@link kSTATUS_CHECKED_DUPS}</tt>: Checked for duplicates, the dataset was
	 * 		verified for duplicate entries.
	 * 	<li><tt>{@link kSTATUS_CHECKED_REFS}</tt>: Checked for invalid references, the
	 * 		dataset was verified for invalid references.
	 * 	<li><tt>{@link kSTATUS_STATS}</tt>: Loaded statistics, statistic informationwas
	 * 		added to the dataset.
	 * 	<li><tt>{@link kSTATUS_VALID}</tt>: Validated, the dataset was validated and loaded
	 * 		into the final collection.
	 * 	<li><tt>{@link kSTATUS_DUPLICATE_COLUMNS}</tt>: Duplicate columns, the dataset has
	 * 		duplicate columns and is not valid.
	 * 	<li><tt>{@link kSTATUS_DUPLICATE_ENTRIES}</tt>: Duplicate entries, the dataset has
	 * 		duplicate entries and is not valid.
	 * 	<li><tt>{@link kSTATUS_INVALID_REFERENCES}</tt>: invalid references, the dataset has
	 * 		invalid references and is not valid.
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_STATUS = 'status';

	/**
	 * <h4>Data dictionary dataset columns.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of dataset columns</em>, it is an array in which the index is the column
	 * cell coordinate and the value is the dataset field name (corresponding header row
	 * value).
	 *
	 * @var string
	 */
	const kDDICT_COLUMNS = 'columns';

	/**
	 * <h4>Data dictionary dataset duplicate columns.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of duplicate dataset columns</em>, it is an array that contains the list
	 * of duplicate header row values.
	 *
	 * @var string
	 */
	const kDDICT_COLUMN_DUPS = 'column_dups';

	/**
	 * <h4>Data dictionary dataset duplicate entries.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of duplicate dataset entries</em>, it is an array structured as
	 * follows:
	 *
	 * <ul>
	 * 	<li><em>index</em>: The array index holds the duplicates group identifier.
	 * 	 <ul>
	 * 		<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding
	 * 			the list of identifiers for the group: the location, team, cluster and unit
	 * 			identifier, and, depending on the unit, the household and/or the mother
	 * 			identifier.
	 * 		<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding
	 * 			the list of duplicate rows for the current group.
	 * 	 </ul>
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_ENTRY_DUPS = 'entry_dups';

	/**
	 * <h4>Data dictionary dataset invalid mother references.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of invalid mother references</em>, it is an array structured as
	 * follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding the
	 * 		list of identifiers for the mother reference: the location, team, cluster,
	 * 		household and mother numbers.
	 * 	<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding the list of
	 * 		rows featuring the invalid reference.
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_INVALID_MOTHERS = 'invalid_mothers';

	/**
	 * <h4>Data dictionary dataset invalid household references.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of invalid household references</em>, it is an array structured as
	 * follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding the
	 * 		list of identifiers for the household reference: the location, team, cluster
	 * 		and household number.
	 * 	<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding the list of
	 * 		rows featuring the invalid reference.
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_INVALID_HOUSEHOLDS = 'invalid_households';

	/**
	 * <h4>Data dictionary dataset fields.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of dataset offsets and types</em>, it is an array structured as follows:
	 *
	 * <ul>
	 * 	<li><em>index</em>: The array index holds the dataset header value corresponding to
	 * 		the field, the value is an array structured as follows:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_FIELD_KIND}</tt>: The data kind inferred when the dataset
	 * 			was loaded.
	 * 		<li><tt>{@link kFIELD_TYPE}</tt>: The data type determined by the user.
	 * 		<li><tt>{@link kFIELD_NAME}</tt>: The standard field name used in the
	 * 			final processed dataset.
	 * 	 </ul>
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_FIELDS = 'fields';

	/**
	 * <h4>Data dictionary field kind.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field data kind</em>. This enumerated value indicates the general data type
	 * of the field and it is set by this class when the dataset is loaded:
	 *
	 * <ul>
	 * 	<li><tt>{@link kTYPE_STRING</tt>: Any non numeric value will imply this kind.
	 * 	<li><tt>{@link kTYPE_DOUBLE</tt>: Any floating point number with a decimal
	 * 		other than <tt>0</tt> will imply this type.
	 * 	<li><tt>{@link kTYPE_INTEGER</tt>: If the set of values is all numeric and
	 * 		does not have a floating point, it implies that all values are of integer type.
	 * </ul>
	 *
	 * @var string
	 */
	const kFIELD_KIND = 'kind';

	/**
	 * <h4>Data dictionary field type.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field data type</em>. This enumerated value indicates the specific data type
	 * of the field and is a user determined value:
	 *
	 * <ul>
	 * 	<li><tt>{@link kTYPE_STRING</tt>: String.
	 * 	<li><tt>{@link kTYPE_DATE</tt>: Date in <tt>YYYY-MM-DD</tt> format.
	 * 	<li><tt>{@link kTYPE_INTEGER</tt>: Integer.
	 * 	<li><tt>{@link kTYPE_DOUBLE</tt>: Floating point number, double by default.
	 * </ul>
	 *
	 * @var string
	 */
	const kFIELD_TYPE = 'type';

	/**
	 * <h4>Data dictionary field format.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field format</em>. This value is used to provide the original format of a
	 * value. In general it is used to format dates, it contains the following values:
	 *
	 * <ul>
	 * 	<li><tt>YYYY-MM-DD</tt>: Year, month and day (the default final format).
	 * 	<li><tt>YY-MM-DD</tt>: Year, month and day.
	 * 	<li><tt>DD-MM-YYYY</tt>: Day, month and year.
	 * 	<li><tt>DD-MM-YY</tt>: Day, month and year.
	 * 	<li><tt>MM-DD-YYYY</tt>: Month, day and year.
	 * 	<li><tt>MM-DD-YY</tt>: Month, day and year.
	 * </ul>
	 *
	 * @var string
	 */
	const kFIELD_FORMAT = 'format';

	/**
	 * <h4>Data dictionary field name.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field default name</em>. This value represents the default or standard field
	 * name that will be used in the final processed datasets.
	 *
	 * @var string
	 */
	const kFIELD_NAME = 'name';

	/**
	 * <h4>Data dictionary field values count.</h4>
	 *
	 * This constant holds the <em>total values count</em> for the current field.
	 *
	 * @var string
	 */
	const kFIELD_COUNT = 'count';

	/**
	 * <h4>Data dictionary distinct field values.</h4>
	 *
	 * This constant holds the <em>distinct values count</em> for the current field.
	 *
	 * @var string
	 */
	const kFIELD_DISTINCT = 'distinct';

	/**
	 * <h4>String type.</h4>
	 *
	 * This constant represents a string data type.
	 *
	 * @var string
	 */
	const kTYPE_STRING = 'string';

	/**
	 * <h4>Integer type.</h4>
	 *
	 * This constant represents a integer data type.
	 *
	 * @var string
	 */
	const kTYPE_INTEGER = 'int';

	/**
	 * <h4>Number kind.</h4>
	 *
	 * This constant represents a number kind, this data type is set when the dataset is
	 * loaded and represents a set of floating point values which do not have decimal
	 * numbers other than <tt>0</tt>: this means that the value may be set to an integer, if
	 * needed.
	 *
	 * @var string
	 */
	const kTYPE_NUMBER = 'number';

	/**
	 * <h4>Double type.</h4>
	 *
	 * This constant represents a double floating point data type.
	 *
	 * @var string
	 */
	const kTYPE_DOUBLE = 'double';

	/**
	 * <h4>Date type.</h4>
	 *
	 * This constant represents a date type, dates will be stored in the <tt>YYYY-MM-DD</tt>
	 * format.
	 *
	 * @var string
	 */
	const kTYPE_DATE = 'date';

	/**
	 * <h4>Duplicate cluster.</h4>
	 *
	 * This constant represents the <em>offset</em> of the <em>{@link kSTATUS_CHECKED_DUPS}
	 * entry in the data dictionary</em> that contains the duplicates cluster. It is an
	 * array containing the location, team, cluster and identifiers of the duplicate entry.
	 *
	 * @var string
	 */
	const kGROUP_CLUSTER = 'group_cluster';

	/**
	 * <h4>Duplicate rows.</h4>
	 *
	 * This constant represents the <em>offset</em> of the <em>{@link kSTATUS_CHECKED_DUPS}
	 * entry in the data dictionary</em> that contains the list of duplicate rows.
	 *
	 * @var string
	 */
	const kGROUP_ROWS = 'group_rows';

	/**
	 * <h4>Dataset idle status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>idle status</em>, it
	 * signifies that the dataset was not yet declared.
	 *
	 * @var int
	 */
	const kSTATUS_IDLE = 0x00000000;

	/**
	 * <h4>Dataset loaded status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>loaded status</em>, it
	 * signifies that the dataset was loaded from the file to the collection.
	 *
	 * @var int
	 */
	const kSTATUS_LOADED = 0x00000001;

	/**
	 * <h4>Dataset checked duplicates status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicates checked
	 * status</em>, it signifies that the dataset has been checked for duplicate entries.
	 *
	 * @var int
	 */
	const kSTATUS_CHECKED_DUPS = 0x00000002;

	/**
	 * <h4>Dataset checked references status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>references checked
	 * status</em>, it signifies that the dataset has been checked for invalid references.
	 *
	 * @var int
	 */
	const kSTATUS_CHECKED_REFS = 0x00000004;

	/**
	 * <h4>Dataset processed stats status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>stats processed
	 * status</em>, it signifies that the dataset holds statistical information.
	 *
	 * @var int
	 */
	const kSTATUS_LOADED_STATS = 0x00000008;

	/**
	 * <h4>Dataset finalised status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>finalised status</em>,
	 * it signifies that the dataset has been validated and written to the final collection.
	 *
	 * @var int
	 */
	const kSTATUS_VALID = 0x00000010;

	/**
	 * <h4>Dataset has duplicate fields status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicate fields
	 * status</em>, it signifies that the dataset has duplicate columns.
	 *
	 * @var int
	 */
	const kSTATUS_DUPLICATE_COLUMNS = 0x00000020;

	/**
	 * <h4>Dataset has duplicates status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicate entries
	 * status</em>, it signifies that the dataset has duplicate entries.
	 *
	 * @var int
	 */
	const kSTATUS_DUPLICATE_ENTRIES = 0x00000040;

	/**
	 * <h4>Dataset has invalid references status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>invalid references
	 * status</em>, it signifies that the dataset has invalid references.
	 *
	 * @var int
	 */
	const kSTATUS_INVALID_REFERENCES = 0x00000080;

	/**
	 * <h4>Client connection.</h4>
	 *
	 * This data member holds the <em>client connection</em>.
	 *
	 * @var MongoDB\Client
	 */
	protected $mClient = NULL;

	/**
	 * <h4>Database connection.</h4>
	 *
	 * This data member holds the <em>database connection</em>.
	 *
	 * @var MongoDB\Database
	 */
	protected $mDatabase = NULL;

	/**
	 * <h4>Survey collection connection.</h4>
	 *
	 * This data member holds the <em>survey collection connection</em>, this will be where
	 * the merged documents will reside.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mSurvey = NULL;

	/**
	 * <h4>Household collection connection.</h4>
	 *
	 * This data member holds the <em>household collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mHousehold = NULL;

	/**
	 * <h4>Mother collection connection.</h4>
	 *
	 * This data member holds the <em>mother collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mMother = NULL;

	/**
	 * <h4>Child collection connection.</h4>
	 *
	 * This data member holds the <em>child collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mChild = NULL;

	/**
	 * <h4>Data dictionary collection connection.</h4>
	 *
	 * This data member holds the <em>data dictionary collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mDDICT = NULL;

	/**
	 * <h4>Data dictionary record.</h4>
	 *
	 * This data member holds the <em>data dictionary record</em>, it is an array that
	 * contains all the information related to household, mother and child datasets.
	 *
	 * The array is structured as follows:
	 *
	 * <ul>
	 * 	<li><em>Unit</em>: This element is an array containing the unit record, the array
	 * 		key identifies the unit:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID</tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID</tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID</tt>: Household dataset.
	 * 	 </ul>
	 * 		Each element is an array containing the following items:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_STATUS</tt>: Dataset <em>status</em>.
	 * 		<li><tt>{@link kDDICT_FIELDS</tt>: Dataset <em>field information</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_FILE</tt>: Dataset <em>file path</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_HEADER</tt>: Dataset <em>header row number</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_DATA</tt>: Dataset <em>data row number</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_DATE</tt>: <em>Survey date</em> header value.
	 * 		<li><tt>{@link kDATASET_OFFSET_LOCATION</tt>: <em>Survey location number</em>
	 * 			header value.
	 * 		<li><tt>{@link kDATASET_OFFSET_TEAM</tt>: <em>Survey team number</em> header
	 * 			value.
	 * 		<li><tt>{@link kDATASET_OFFSET_CLUSTER</tt>: <em>Survey cluster number</em>
	 * 			header value.
	 * 		<li><tt>{@link kDATASET_OFFSET_IDENTIFIER</tt>: <em>Unit number</em> header
	 * 			value, this corresponds to the household number in the household dataset and
	 * 			the same for mother and child datasets.
	 * 		<li><tt>{@link kDATASET_OFFSET_HOUSEHOLD_ID</tt>: <em>Household number</em>
	 * 			header value in mother and child datasets.
	 * 		<li><tt>{@link kDATASET_OFFSET_MOTHER_ID</tt>: <em>Mother number</em> header
	 * 			value in child dataset.
	 * 	 </ul>
	 * </ul>
	 *
	 * This information is stored in the {@link kNAME_DDICT} collection, it is loaded from
	 * the database when the object is instantiated and stored when the object is
	 * desctructed.
	 *
	 * @var array
	 */
	protected $mDDICTInfo = NULL;




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
	 * The object can be instantiated by providing the database connection information:
	 *
	 * <ul>
	 * 	<li><b>$theDSN</b>: The client data source name (defaults to {@link kDSN}.
	 * 	<li><b>$theDatabase</b>: The database name.
	 * </ul>
	 *
	 * Once instantiated, the method will attempt to load the data dictionary from the
	 * database, if it is not found, the method will initialise it.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param string				$theDSN				Data source name.
	 *
	 * @uses Client()
	 * @uses Database()
	 * @uses Dictionary()
	 * @uses Survey()
	 * @uses Household()
	 * @uses Mother()
	 * @uses Child()
	 * @uses InitDictionary()
	 */
	public function __construct( $theDatabase, $theDSN = self::kNAME_DSN )
	{
		//
		// Create client.
		//
		$this->Client( $theDSN );

		//
		// Create database.
		//
		$this->Database( $theDatabase );

		//
		// Set collections.
		//
		$this->Dictionary( self::kNAME_DDICT );
		$this->Survey( self::kNAME_SURVEY );
		$this->Household( self::kNAME_HOUSEHOLD );
		$this->Mother( self::kNAME_MOTHER );
		$this->Child( self::kNAME_CHILD );

		//
		// Initialise data dictionary.
		//
		$this->InitDictionary();

	} // Constructor.


	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * <h4>Destruct class.</h4>
	 *
	 * The object can be instantiated by providing the database connection information:
	 *
	 * <ul>
	 * 	<li><b>$theDSN</b>: The client data source name (defaults to {@link kDSN}.
	 * 	<li><b>$theDatabase</b>: The database name (defaults to {@link kNAME_DATABASE}.
	 * </ul>
	 *
	 * Once instantiated, all other elements can be set via accessor methods.
	 *
	 * @param string				$theDSN				Data source name.
	 * @param string				$theDatabase		Database name.
	 *
	 * @uses SaveDictionary()
	 */
	public function __destruct()
	{
		//
		// Save data dictionary.
		//
		$this->SaveDictionary();

	} // Destructor.



/*=======================================================================================
 *																						*
 *						PUBLIC DATABASE MEMBER ACCESSOR INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Client																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve client.</h4>
	 *
	 * This method can be used to set or retrieve the database client, if you provide a
	 * string, it will be interpreted as the client data source name, if you provide
	 * <tt>NULL</tt>, the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Client
	 * @throws InvalidArgumentException
	 */
	public function Client( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mClient;													// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Client cannot be deleted." );									// !@! ==>

		return $this->mClient = new Client( (string)$theValue );					// ==>

	} // Client.


	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve database.</h4>
	 *
	 * This method can be used to set or retrieve the database connection, if you provide a
	 * string, it will be interpreted as the database name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Database
	 * @throws InvalidArgumentException
	 */
	public function Database( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mDatabase;												// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Database cannot be deleted." );								// !@! ==>

		return
			$this->mDatabase
				= $this->Client()->selectDatabase( (string)$theValue );				// ==>

	} // Database.


	/*===================================================================================
	 *	Dictionary																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve data dictionary collection.</h4>
	 *
	 * This method can be used to set or retrieve the data dictionary collection, if you
	 * provide a string, it will be interpreted as the collection name, if you provide
	 * <tt>NULL</tt>, the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Dictionary( $theValue = NULL )
	{
		return $this->manageCollection( $this->mDDICT, $theValue );					// ==>

	} // Dictionary.


	/*===================================================================================
	 *	Survey																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve survey collection.</h4>
	 *
	 * This method can be used to set or retrieve the survey collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Survey( $theValue = NULL )
	{
		return $this->manageCollection( $this->mSurvey, $theValue );				// ==>

	} // Survey.


	/*===================================================================================
	 *	Household																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household collection.</h4>
	 *
	 * This method can be used to set or retrieve the household collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Household( $theValue = NULL )
	{
		return $this->manageCollection( $this->mHousehold, $theValue );				// ==>

	} // Household.


	/*===================================================================================
	 *	Mother																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother collection.</h4>
	 *
	 * This method can be used to set or retrieve the mother collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Mother( $theValue = NULL )
	{
		return $this->manageCollection( $this->mMother, $theValue );				// ==>

	} // Mother.


	/*===================================================================================
	 *	Child																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child collection.</h4>
	 *
	 * This method can be used to set or retrieve the child collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Child( $theValue = NULL )
	{
		return $this->manageCollection( $this->mChild, $theValue );					// ==>

	} // Child.



/*=======================================================================================
 *																						*
 *						PUBLIC DICTIONARY MEMBER ACCESSOR INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	DataDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Retrieve the datadictionary.</h4>
	 *
	 * This method can be used to retrieve a copy of the current data dictionary.
	 *
	 * @return array				Data dictionary.
	 */
	public function DataDictionary( $theValue = NULL )
	{
		return $this->mDDICTInfo;													// ==>

	} // DataDictionary.


	/*===================================================================================
	 *	ChildDataset																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child dataset.</h4>
	 *
	 * This method can be used to manage the child dataset file, it expects a single
	 * parameter that represents the new dataset file path, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current dataset path.
	 * 	<li><i>string</i>: The file path to set a new file.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return string				Dataset file path.
	 *
	 * @uses datasetPath()
	 */
	public function ChildDataset( $theValue = NULL )
	{
		return $this->datasetPath( self::kDDICT_CHILD_ID, $theValue );				// ==>

	} // ChildDataset.


	/*===================================================================================
	 *	MotherDataset																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother dataset.</h4>
	 *
	 * This method can be used to manage the mother dataset file, it expects a single
	 * parameter that represents the new dataset file path, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current dataset path.
	 * 	<li><i>string</i>: The file path to set a new file.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return string				Dataset file path.
	 *
	 * @uses datasetPath()
	 */
	public function MotherDataset( $theValue = NULL )
	{
		return $this->datasetPath( self::kDDICT_MOTHER_ID, $theValue );				// ==>

	} // MotherDataset.


	/*===================================================================================
	 *	HouseholdDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household dataset.</h4>
	 *
	 * This method can be used to manage the household dataset file, it expects a single
	 * parameter that represents the new dataset file path, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current dataset path.
	 * 	<li><i>string</i>: The file path to set a new file.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return string				Dataset file path.
	 *
	 * @uses datasetPath()
	 */
	public function HouseholdDataset( $theValue = NULL )
	{
		return $this->datasetPath( self::kDDICT_HOUSEHOLD_ID, $theValue );			// ==>

	} // HouseholdDataset.


	/*===================================================================================
	 *	ChildDatasetStatus																*
	 *==================================================================================*/

	/**
	 * <h4>Retrieve child dataset status.</h4>
	 *
	 * This method can be used to get the child dataset status.
	 *
	 * @return int					Dataset status code.
	 *
	 * @uses datasetStatus()
	 */
	public function ChildDatasetStatus( $theValue = NULL )
	{
		return $this->datasetStatus( self::kDDICT_CHILD_ID );						// ==>

	} // ChildDatasetStatus.


	/*===================================================================================
	 *	MotherDatasetStatus																*
	 *==================================================================================*/

	/**
	 * <h4>Retrieve mother dataset.</h4>
	 *
	 * This method can be used to get the mother dataset status.
	 *
	 * @return int					Dataset status code.
	 *
	 * @uses datasetStatus()
	 */
	public function MotherDatasetStatus( $theValue = NULL )
	{
		return $this->datasetStatus( self::kDDICT_MOTHER_ID );						// ==>

	} // MotherDatasetStatus.


	/*===================================================================================
	 *	HouseholdDatasetStatus															*
	 *==================================================================================*/

	/**
	 * <h4>Retrieve household dataset.</h4>
	 *
	 * This method can be used to get the household dataset status.
	 *
	 * @return int					Dataset status code.
	 *
	 * @uses datasetStatus()
	 */
	public function HouseholdDatasetStatus( $theValue = NULL )
	{
		return $this->datasetStatus( self::kDDICT_HOUSEHOLD_ID );					// ==>

	} // HouseholdDatasetStatus.


	/*===================================================================================
	 *	ChildDatasetHeaderRow																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child dataset header row.</h4>
	 *
	 * This method can be used to manage the child dataset header row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current header row number.
	 * 	<li><i>integer</i>: The new header row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset header row number.
	 *
	 * @uses datasetHeaderRow()
	 */
	public function ChildDatasetHeaderRow( $theValue = NULL )
	{
		return $this->datasetHeaderRow( self::kDDICT_CHILD_ID, $theValue );			// ==>

	} // ChildDatasetHeaderRow.


	/*===================================================================================
	 *	MotherDatasetHeaderRow															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother dataset header row.</h4>
	 *
	 * This method can be used to manage the mother dataset header row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current header row number.
	 * 	<li><i>integer</i>: The new header row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset header row number.
	 *
	 * @uses datasetHeaderRow()
	 */
	public function MotherDatasetHeaderRow( $theValue = NULL )
	{
		return $this->datasetHeaderRow( self::kDDICT_MOTHER_ID, $theValue );		// ==>

	} // MotherDatasetHeaderRow.


	/*===================================================================================
	 *	HouseholdDatasetHeaderRow														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household dataset header row.</h4>
	 *
	 * This method can be used to manage the household dataset header row number, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current header row number.
	 * 	<li><i>integer</i>: The new header row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset header row number.
	 *
	 * @uses datasetHeaderRow()
	 */
	public function HouseholdDatasetHeaderRow( $theValue = NULL )
	{
		return $this->datasetHeaderRow( self::kDDICT_HOUSEHOLD_ID, $theValue );		// ==>

	} // HouseholdDatasetHeaderRow.


	/*===================================================================================
	 *	ChildDatasetDataRow																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child dataset data row.</h4>
	 *
	 * This method can be used to manage the child dataset data row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current data row number.
	 * 	<li><i>integer</i>: The new data row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset data row number.
	 *
	 * @uses datasetDataRow()
	 */
	public function ChildDatasetDataRow( $theValue = NULL )
	{
		return $this->datasetDataRow( self::kDDICT_CHILD_ID, $theValue );			// ==>

	} // ChildDatasetDataRow.


	/*===================================================================================
	 *	MotherDatasetDataRow															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother dataset data row.</h4>
	 *
	 * This method can be used to manage the mother dataset data row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current data row number.
	 * 	<li><i>integer</i>: The new data row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset data row number.
	 *
	 * @uses datasetDataRow()
	 */
	public function MotherDatasetDataRow( $theValue = NULL )
	{
		return $this->datasetDataRow( self::kDDICT_MOTHER_ID, $theValue );			// ==>

	} // MotherDatasetDataRow.


	/*===================================================================================
	 *	HouseholdDatasetDataRow															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household dataset data row.</h4>
	 *
	 * This method can be used to manage the household dataset data row number, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current data row number.
	 * 	<li><i>integer</i>: The new data row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset data row number.
	 *
	 * @uses datasetDataRow()
	 */
	public function HouseholdDatasetDataRow( $theValue = NULL )
	{
		return $this->datasetDataRow( self::kDDICT_HOUSEHOLD_ID, $theValue );		// ==>

	} // HouseholdDatasetDataRow.


	/*===================================================================================
	 *	ChildDatasetDateOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey date offset.</h4>
	 *
	 * This method can be used to manage the child survey date offset, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey date offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetDateOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID,
			self::kDATASET_OFFSET_DATE,
			$theValue
		);																			// ==>

	} // ChildDatasetDateOffset.


	/*===================================================================================
	 *	MotherDatasetDateOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey date offset.</h4>
	 *
	 * This method can be used to manage the mother survey date offset, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey date offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetDateOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID,
			self::kDATASET_OFFSET_DATE,
			$theValue
		);																			// ==>

	} // MotherDatasetDateOffset.


	/*===================================================================================
	 *	HouseholdDatasetDateOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey date offset.</h4>
	 *
	 * This method can be used to manage the household survey date offset, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey date offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetDateOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDATASET_OFFSET_DATE,
			$theValue
		);																			// ==>

	} // HouseholdDatasetDateOffset.


	/*===================================================================================
	 *	ChildDatasetLocationOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey location offset.</h4>
	 *
	 * This method can be used to manage the child survey location number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey location number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetLocationOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID,
			self::kDATASET_OFFSET_LOCATION,
			$theValue
		);																			// ==>

	} // ChildDatasetLocationOffset.


	/*===================================================================================
	 *	MotherDatasetLocationOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey location offset.</h4>
	 *
	 * This method can be used to manage the mother survey location number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey location number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetLocationOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID,
			self::kDATASET_OFFSET_LOCATION,
			$theValue
		);																			// ==>

	} // MotherDatasetLocationOffset.


	/*===================================================================================
	 *	HouseholdDatasetLocationOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey location offset.</h4>
	 *
	 * This method can be used to manage the household survey location number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey location number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetLocationOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDATASET_OFFSET_LOCATION,
			$theValue
		);																			// ==>

	} // HouseholdDatasetLocationOffset.


	/*===================================================================================
	 *	ChildDatasetTeamOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey team offset.</h4>
	 *
	 * This method can be used to manage the child survey team number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey team number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetTeamOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID,
			self::kDATASET_OFFSET_TEAM,
			$theValue
		);																			// ==>

	} // ChildDatasetTeamOffset.


	/*===================================================================================
	 *	MotherDatasetTeamOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey team offset.</h4>
	 *
	 * This method can be used to manage the mother survey team number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey team number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetTeamOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID,
			self::kDATASET_OFFSET_TEAM,
			$theValue
		);																			// ==>

	} // MotherDatasetTeamOffset.


	/*===================================================================================
	 *	HouseholdDatasetTeamOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey team offset.</h4>
	 *
	 * This method can be used to manage the household survey team number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey team number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetTeamOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDATASET_OFFSET_TEAM,
			$theValue
		);																			// ==>

	} // HouseholdDatasetTeamOffset.


	/*===================================================================================
	 *	ChildDatasetClusterOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey cluster offset.</h4>
	 *
	 * This method can be used to manage the child survey cluster number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey cluster number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetClusterOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID,
			self::kDATASET_OFFSET_CLUSTER,
			$theValue
		);																			// ==>

	} // ChildDatasetClusterOffset.


	/*===================================================================================
	 *	MotherDatasetClusterOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey cluster offset.</h4>
	 *
	 * This method can be used to manage the mother survey cluster number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey cluster number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetClusterOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID,
			self::kDATASET_OFFSET_CLUSTER,
			$theValue
		);																			// ==>

	} // MotherDatasetClusterOffset.


	/*===================================================================================
	 *	HouseholdDatasetClusterOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey cluster offset.</h4>
	 *
	 * This method can be used to manage the household survey cluster number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey cluster number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetClusterOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDATASET_OFFSET_CLUSTER,
			$theValue
		);																			// ==>

	} // HouseholdDatasetClusterOffset.


	/*===================================================================================
	 *	ChildDatasetIdentifierOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey identifier offset.</h4>
	 *
	 * This method can be used to manage the child survey identifier number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey identifier number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetIdentifierOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID,
			self::kDATASET_OFFSET_IDENTIFIER,
			$theValue
		);																			// ==>

	} // ChildDatasetIdentifierOffset.


	/*===================================================================================
	 *	MotherDatasetIdentifierOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey identifier offset.</h4>
	 *
	 * This method can be used to manage the mother survey identifier number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey identifier number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetIdentifierOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID,
			self::kDATASET_OFFSET_IDENTIFIER,
			$theValue
		);																			// ==>

	} // MotherDatasetIdentifierOffset.


	/*===================================================================================
	 *	HouseholdDatasetIdentifierOffset												*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey identifier offset.</h4>
	 *
	 * This method can be used to manage the household survey identifier number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey identifier number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetIdentifierOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDATASET_OFFSET_IDENTIFIER,
			$theValue
		);																			// ==>

	} // HouseholdDatasetIdentifierOffset.


	/*===================================================================================
	 *	ChildDatasetHouseholdOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey household offset.</h4>
	 *
	 * This method can be used to manage the child survey household number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey household number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetHouseholdOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID,
			self::kDATASET_OFFSET_HOUSEHOLD,
			$theValue
		);																			// ==>

	} // ChildDatasetHouseholdOffset.


	/*===================================================================================
	 *	MotherDatasetHouseholdOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey household offset.</h4>
	 *
	 * This method can be used to manage the mother survey household number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey household number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetHouseholdOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID,
			self::kDATASET_OFFSET_HOUSEHOLD,
			$theValue
		);																			// ==>

	} // MotherDatasetHouseholdOffset.


	/*===================================================================================
	 *	ChildDatasetMotherOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey mother offset.</h4>
	 *
	 * This method can be used to manage the child survey mother number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey mother number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetMotherOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID,
			self::kDATASET_OFFSET_MOTHER,
			$theValue
		);																			// ==>

	} // ChildDatasetMotherOffset.


	/*===================================================================================
	 *	ChildDatasetHeaderCoumns														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child header columns.</h4>
	 *
	 * This method can be used to manage the child data dictionary header columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header columns.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetHeaderCoumns( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID,
			self::kDDICT_COLUMNS,
			$theValue
		);																			// ==>

	} // ChildDatasetHeaderCoumns.


	/*===================================================================================
	 *	MotherDatasetHeaderCoumns														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother header columns.</h4>
	 *
	 * This method can be used to manage the mother data dictionary header columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header columns.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetHeaderCoumns( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_MOTHER_ID,
			self::kDDICT_COLUMNS,
			$theValue
		);																			// ==>

	} // MotherDatasetHeaderCoumns.


	/*===================================================================================
	 *	HouseholdDatasetHeaderCoumns													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household header columns.</h4>
	 *
	 * This method can be used to manage the household data dictionary header columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header columns.
	 *
	 * @uses dictionaryList()
	 */
	public function HouseholdDatasetHeaderCoumns( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDDICT_COLUMNS,
			$theValue
		);																			// ==>

	} // HouseholdDatasetHeaderCoumns.


	/*===================================================================================
	 *	ChildDatasetDuplicateHeaderCoumns												*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child header duplicate columns.</h4>
	 *
	 * This method can be used to manage the child data dictionary header duplicate columns,
	 * this element holds the list of values that appear more than once in the header row.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header duplicate columns.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetDuplicateHeaderCoumns( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID,
			self::kDDICT_COLUMN_DUPS,
			$theValue
		);																			// ==>

	} // ChildDatasetDuplicateHeaderCoumns.


	/*===================================================================================
	 *	MotherDatasetDuplicateHeaderCoumns												*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother header duplicate columns.</h4>
	 *
	 * This method can be used to manage the mother data dictionary header duplicate columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header duplicate columns.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetDuplicateHeaderCoumns( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID,
			self::kDDICT_COLUMN_DUPS,
			$theValue
		);																			// ==>

	} // MotherDatasetDuplicateHeaderCoumns.


	/*===================================================================================
	 *	HouseholdDatasetDuplicateHeaderCoumns											*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household header duplicate columns.</h4>
	 *
	 * This method can be used to manage the household data dictionary header duplicate columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header duplicate columns.
	 *
	 * @uses dictionaryList()
	 */
	public function HouseholdDatasetDuplicateHeaderCoumns( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDDICT_COLUMN_DUPS,
			$theValue
		);																			// ==>

	} // HouseholdDatasetDuplicateHeaderCoumns.


	/*===================================================================================
	 *	ChildDatasetFields																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child fields.</h4>
	 *
	 * This method can be used to manage the child data dictionary fields, this
	 * element holds the list of field names and types.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset fields.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetFields( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID,
			self::kDDICT_FIELDS,
			$theValue
		);																			// ==>

	} // ChildDatasetFields.


	/*===================================================================================
	 *	MotherDatasetFields																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother fields.</h4>
	 *
	 * This method can be used to manage the mother data dictionary fields, this
	 * element holds the list of field names and types.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset fields.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetFields( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_MOTHER_ID,
			self::kDDICT_FIELDS,
			$theValue
		);																			// ==>

	} // MotherDatasetFields.


	/*===================================================================================
	 *	HouseholdDatasetFields															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household fields.</h4>
	 *
	 * This method can be used to manage the household data dictionary fields, this
	 * element holds the list of field names and types.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset fields.
	 *
	 * @uses dictionaryList()
	 */
	public function HouseholdDatasetFields( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDDICT_FIELDS,
			$theValue
		);																			// ==>

	} // HouseholdDatasetFields.


	/*===================================================================================
	 *	ChildDatasetDuplicateEntries													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child duplicate entries list.</h4>
	 *
	 * This method can be used to manage the child duplicate entries list, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return array				Dataset duplicate entries list.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetDuplicateEntries( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID,
			self::kDDICT_ENTRY_DUPS,
			$theValue
		);																			// ==>

	} // ChildDatasetDuplicateEntries.


	/*===================================================================================
	 *	MotherDatasetDuplicateEntries													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey cluster offset.</h4>
	 *
	 * This method can be used to manage the mother duplicate entries list, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset duplicate entries list.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetDuplicateEntries( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_MOTHER_ID,
			self::kDDICT_ENTRY_DUPS,
			$theValue
		);																			// ==>

	} // MotherDatasetDuplicateEntries.


	/*===================================================================================
	 *	HouseholdDatasetDuplicateEntries												*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey cluster offset.</h4>
	 *
	 * This method can be used to manage the household duplicate entries list, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset duplicate entries list.
	 *
	 * @uses dictionaryList()
	 */
	public function HouseholdDatasetDuplicateEntries( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDDICT_ENTRY_DUPS,
			$theValue
		);																			// ==>

	} // HouseholdDatasetDuplicateEntries.


	/*===================================================================================
	 *	ChildDatasetInvalidMothers														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child invalid mother references.</h4>
	 *
	 * This method can be used to manage the child data dictionary invalid mother
	 * references, this element holds the list of invalid mother references.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Child dataset invalid mother references.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetInvalidMothers( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID,
			self::kDDICT_INVALID_MOTHERS,
			$theValue
		);																			// ==>

	} // ChildDatasetInvalidMothers.


	/*===================================================================================
	 *	ChildDatasetInvalidHouseholds													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child invalid household references.</h4>
	 *
	 * This method can be used to manage the child data dictionary invalid household
	 * references, this element holds the list of invalid household references.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Child dataset invalid household references.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetInvalidHouseholds( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID,
			self::kDDICT_INVALID_HOUSEHOLDS,
			$theValue
		);																			// ==>

	} // ChildDatasetInvalidHouseholds.


	/*===================================================================================
	 *	MotherDatasetInvalidHouseholds													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother invalid household references.</h4>
	 *
	 * This method can be used to manage the mother data dictionary invalid household
	 * references, this element holds the list of invalid household references.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Mother dataset invalid household references.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetInvalidHouseholds( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_MOTHER_ID,
			self::kDDICT_INVALID_HOUSEHOLDS,
			$theValue
		);																			// ==>

	} // MotherDatasetInvalidHouseholds.



/*=======================================================================================
 *																						*
 *								PUBLIC INITIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	InitDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise data dictionary.</h4>
	 *
	 * The duty of this method is to set the {@link mDDICTInfo} data member with the data
	 * dictionary, it will first attempt to load it from the database, if the data
	 * dictionary collection is empty, the method will initialise and set the dictionary.
	 *
	 * If the dictionary data member is already set, not <tt>NULL</tt>, the method will
	 * return <tt>NULL</tt>; if the dictionary was either loaded or initialised, the method
	 * will return <tt>TRUE</tt>.
	 *
	 * Any error will trigger an exception.
	 *
	 * @uses newDataDictionary()
	 */
	public function InitDictionary()
	{
		//
		// Check data dictionary.
		//
		if( ! is_array( $this->mDDICTInfo ) )
		{
			//
			// Init local storage.
			//
			$datasets = [
				self::kDDICT_CHILD_ID,
				self::kDDICT_MOTHER_ID,
				self::kDDICT_HOUSEHOLD_ID
			];

			//
			// Initialise data dictionary.
			//
			foreach( $datasets as $dataset )
			{
				$document = $this->mDDICT->findOne( [ '_id' => $dataset ] );
				$this->mDDICTInfo[ $dataset ]
					= ( $document === NULL )
					? $this->newDataDictionary( $dataset )
					: $document->getArrayCopy();
			}

			return TRUE;															// ==>

		} // Data member not set.

		return NULL;																// ==>

	} // InitDictionary.


	/*===================================================================================
	 *	ResetDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Reset data dictionary.</h4>
	 *
	 * The duty of this method is to set the {@link mDDICTInfo} data member to an idle state
	 * and update the database stored copy.
	 *
	 * Use this method to reset the data dictionary.
	 *
	 * @uses newDataDictionary()
	 */
	public function ResetDictionary()
	{
		//
		// Init local storage.
		//
		$datasets = [
			self::kDDICT_CHILD_ID,
			self::kDDICT_MOTHER_ID,
			self::kDDICT_HOUSEHOLD_ID
		];

		//
		// Reset in database.
		//
		$this->mDDICT->deleteMany( [] );

		//
		// Reset data dictionary.
		//
		foreach( $datasets as $dataset )
		{
			//
			// Get idle record.
			//
			$document = $this->newDataDictionary( $dataset );

			//
			// Reset data member.
			//
			$this->mDDICTInfo[ $dataset ] = $document;

			//
			// Store data member.
			//
			$this->mDDICT->insertOne( $document );
		}

	} // ResetDictionary.


	/*===================================================================================
	 *	SaveDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Save data dictionary.</h4>
	 *
	 * The duty of this method is to store the {@link mDDICTInfo} data member into the
	 * {@link kNAME_DDICT} collection.
	 */
	public function SaveDictionary()
	{
		//
		// Check data member.
		//
		if( is_array( $this->mDDICTInfo ) )
		{
			//
			// Collect data.
			//
			$records = [];
			foreach( $this->mDDICTInfo as $dataset => $dictionary )
				$records[] = $dictionary;

			//
			// Clear existing data dictionary.
			// MILKO - Had to replace replaceOne() with the below,
			//		   because bulk write didn't work.
			//
			if( $this->mDDICT->count() )
				$this->mDDICT->deleteMany( [] );

			//
			// Insert dictionary.
			//
			$this->mDDICT->insertMany( $records );
		}

	} // SaveDictionary.



/*=======================================================================================
 *																						*
 *							PUBLIC FILE IMPORT INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	LoadChildDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset.</h4>
	 *
	 * This method can be used to load the child dataset into the database, the original
	 * file will be stored in a collection named with {@link kNAME_PREFIX_ORIGINAL} as
	 * prefix and {@link kNAME_CHILD} as suffix.
	 *
	 * The collection will feature the row number as the ID and the column as the field
	 * name, the method will return the status code {@link kSTATUS_LOADED} or raise an
	 * exception if the file was not declared.
	 *
	 * @return string				Status code.
	 *
	 * @uses loadDataset()
	 */
	public function LoadChildDataset()
	{
		return $this->loadDataset( self::kDDICT_CHILD_ID );							// ==>

	} // LoadChildDataset.


	/*===================================================================================
	 *	LoadMotherDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset.</h4>
	 *
	 * This method can be used to load the mother dataset into the database, the original
	 * file will be stored in a collection named with {@link kNAME_PREFIX_ORIGINAL} as
	 * prefix and {@link kNAME_MOTHER} as suffix.
	 *
	 * The collection will feature the row number as the ID and the column as the field
	 * name, the method will return the status code {@link kSTATUS_LOADED} or raise an
	 * exception if the file was not declared.
	 *
	 * @return string				Status code.
	 *
	 * @uses loadDataset()
	 */
	public function LoadMotherDataset()
	{
		return $this->loadDataset( self::kDDICT_MOTHER_ID );						// ==>

	} // LoadMotherDataset.


	/*===================================================================================
	 *	LoadHouseholdDataset															*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset.</h4>
	 *
	 * This method can be used to load the household dataset into the database, the original
	 * file will be stored in a collection named with {@link kNAME_PREFIX_ORIGINAL} as
	 * prefix and {@link kNAME_HOUSEHOLD} as suffix.
	 *
	 * The collection will feature the row number as the ID and the column as the field
	 * name, the method will return the status code {@link kSTATUS_LOADED} or raise an
	 * exception if the file was not declared.
	 *
	 * @return string				Status code.
	 *
	 * @uses loadDataset()
	 */
	public function LoadHouseholdDataset()
	{
		return $this->loadDataset( self::kDDICT_HOUSEHOLD_ID );						// ==>

	} // LoadHouseholdDataset.



/*=======================================================================================
 *																						*
 *							PUBLIC VALIDATION INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	LoadChildDatasetHeader															*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset header.</h4>
	 *
	 * This method can be used to load the child dataset header into the data dictionary,
	 * the method will process the header row and check whether it contains duplicate
	 * values, in which case it will return the {@link kSTATUS_DUPLICATE_COLUMNS} status
	 * code; if there are no errors, the method will return the current status code.
	 *
	 * @return string				Status code.
	 *
	 * @uses loadDatasetHeader()
	 */
	public function LoadChildDatasetHeader()
	{
		return $this->loadDatasetHeader( self::kDDICT_CHILD_ID );					// ==>

	} // LoadChildDatasetHeader.


	/*===================================================================================
	 *	LoadMotherDatasetHeader															*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset header.</h4>
	 *
	 * This method can be used to load the mother dataset header into the data dictionary,
	 * the method will process the header row and check whether it contains duplicate
	 * values, in which case it will return the {@link kSTATUS_DUPLICATE_COLUMNS} status
	 * code; if there are no errors, the method will return the current status code.
	 *
	 * @return string				Status code.
	 *
	 * @uses loadDatasetHeader()
	 */
	public function LoadMotherDatasetHeader()
	{
		return $this->loadDatasetHeader( self::kDDICT_MOTHER_ID );					// ==>

	} // LoadMotherDatasetHeader.


	/*===================================================================================
	 *	LoadHouseholdDatasetHeader														*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset header.</h4>
	 *
	 * This method can be used to load the household dataset header into the data
	 * dictionary, the method will process the header row and check whether it contains
	 * duplicate values, in which case it will return the {@link kSTATUS_DUPLICATE_COLUMNS}
	 * status code; if there are no errors, the method will return the current status code.
	 *
	 * @return string				Status code.
	 *
	 * @uses loadDatasetHeader()
	 */
	public function LoadHouseholdDatasetHeader()
	{
		return $this->loadDatasetHeader( self::kDDICT_HOUSEHOLD_ID );				// ==>

	} // LoadHouseholdDatasetHeader.


	/*===================================================================================
	 *	LoadChildDatasetFields															*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset fields.</h4>
	 *
	 * This method can be used to load the child dataset fields into the data dictionary,
	 * the method will process the dataset columns and determine the data type of each
	 * column; if the columns have not yet been loaded, the method will raise an exception.
	 *
	 * @uses loadDatasetFields()
	 */
	public function LoadChildDatasetFields()
	{
		$this->loadDatasetFields( self::kDDICT_CHILD_ID );							// ==>

	} // LoadChildDatasetFields.


	/*===================================================================================
	 *	LoadMotherDatasetFields															*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset fields.</h4>
	 *
	 * This method can be used to load the mother dataset fields into the data dictionary,
	 * the method will process the dataset columns and determine the data type of each
	 * column; if the columns have not yet been loaded, the method will raise an exception.
	 *
	 * @uses loadDatasetFields()
	 */
	public function LoadMotherDatasetFields()
	{
		$this->loadDatasetFields( self::kDDICT_MOTHER_ID );							// ==>

	} // LoadMotherDatasetFields.


	/*===================================================================================
	 *	LoadHouseholdDatasetFields														*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset fields.</h4>
	 *
	 * This method can be used to load the household dataset fields into the data
	 * the method will process the dataset columns and determine the data type of each
	 * column; if the columns have not yet been loaded, the method will raise an exception.
	 *
	 * @uses loadDatasetFields()
	 */
	public function LoadHouseholdDatasetFields()
	{
		$this->loadDatasetFields( self::kDDICT_HOUSEHOLD_ID );						// ==>

	} // LoadHouseholdDatasetFields.


	/*===================================================================================
	 *	LoadChildDatasetData															*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset data.</h4>
	 *
	 * This method can be used to load the child dataset data into the final collection,
	 * the method will iterate the original collection and load all data into the final
	 * collection; if the original collection have not yet been loaded, the method will
	 * raise an exception.
	 *
	 * @uses loadDatasetData()
	 */
	public function LoadChildDatasetData()
	{
		$this->loadDatasetData( self::kDDICT_CHILD_ID );							// ==>

	} // LoadChildDatasetData.


	/*===================================================================================
	 *	LoadMotherDatasetData															*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset data.</h4>
	 *
	 * This method can be used to load the mother dataset data into the final collection,
	 * the method will iterate the original collection and load all data into the final
	 * collection; if the original collection have not yet been loaded, the method will
	 * raise an exception.
	 *
	 * @uses loadDatasetData()
	 */
	public function LoadMotherDatasetData()
	{
		$this->loadDatasetData( self::kDDICT_MOTHER_ID );							// ==>

	} // LoadMotherDatasetData.


	/*===================================================================================
	 *	LoadHouseholdDatasetData														*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset data.</h4>
	 *
	 * This method can be used to load the household dataset data into the data
	 * the method will iterate the original collection and load all data into the final
	 * collection; if the original collection have not yet been loaded, the method will
	 * raise an exception.
	 *
	 * @uses loadDatasetData()
	 */
	public function LoadHouseholdDatasetData()
	{
		$this->loadDatasetData( self::kDDICT_HOUSEHOLD_ID );						// ==>

	} // LoadHouseholdDatasetData.


	/*===================================================================================
	 *	CheckChildDatasetDuplicates														*
	 *==================================================================================*/

	/**
	 * <h4>Check child dataset duplicates.</h4>
	 *
	 * This method can be used to check the child dataset duplicate entries, the method will
	 * scan for duplicate entries and write them to the {@link kDDICT_ENTRY_DUPS} entry in
	 * the data dictionary, in which case it will return the
	 * {@link kSTATUS_DUPLICATE_ENTRIES} code, or {@link kSTATUS_CHECKED_DUPS} code if there
	 * are no duplicate entries.
	 *
	 * When writing duplicate entries it will fill the data dictionary
	 * {@link kDDICT_ENTRY_DUPS} entry as follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kTYPE_DUPLICATES_CLUSTER}</tt>: It contains an array having as key
	 *		the column offset and as value the column value of the location, team, cluster,
	 *		identifier and, if relevant, the household and/or the mother identifiers.
	 *	<li><tt>{@link kTYPE_DUPLICATES_ROWS}</tt>: It contains an array holding the list
	 *		of rows where the duplicates can be found.
	 * </ul>
	 *
	 * The array key of the {@link kDDICT_ENTRY_DUPS} entry represents the duplicate
	 * groups identifier.
	 *
	 * @return int					Status code.
	 *
	 * @uses checkDatasetDuplicateEntries()
	 */
	public function CheckChildDatasetDuplicates()
	{
		return $this->checkDatasetDuplicateEntries( self::kDDICT_CHILD_ID );		// ==>

	} // CheckChildDatasetDuplicates.


	/*===================================================================================
	 *	CheckMotherDatasetDuplicates													*
	 *==================================================================================*/

	/**
	 * <h4>Check mother dataset duplicates.</h4>
	 *
	 * This method can be used to check the mother dataset duplicate entries, the method
	 * will can for duplicate entries and write them to the {@link kDDICT_ENTRY_DUPS} entry
	 * in the data dictionary, in which case it will return the
	 * {@link kSTATUS_DUPLICATE_ENTRIES} code, or {@link kSTATUS_CHECKED_DUPS} code if there
	 * are no duplicate entries.
	 *
	 * When writing duplicate entries it will fill the data dictionary
	 * {@link kDDICT_ENTRY_DUPS} entry as follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kTYPE_DUPLICATES_CLUSTER}</tt>: It contains an array having as key
	 *		the column offset and as value the column value of the location, team, cluster,
	 *		identifier and, if relevant, the household and/or the mother identifiers.
	 *	<li><tt>{@link kTYPE_DUPLICATES_ROWS}</tt>: It contains an array holding the list
	 *		of rows where the duplicates can be found.
	 * </ul>
	 *
	 * The array key of the {@link kDDICT_ENTRY_DUPS} entry represents the duplicate
	 * groups identifier.
	 *
	 * @return int					Status code.
	 *
	 * @uses checkDatasetDuplicateEntries()
	 */
	public function CheckMotherDatasetDuplicates()
	{
		return $this->checkDatasetDuplicateEntries( self::kDDICT_MOTHER_ID );		// ==>

	} // CheckMotherDatasetDuplicates.


	/*===================================================================================
	 *	CheckHouseholdDatasetDuplicates													*
	 *==================================================================================*/

	/**
	 * <h4>Check household dataset duplicates.</h4>
	 *
	 * This method can be used to check the mother dataset duplicate entries, the method
	 * will can for duplicate entries and write them to the {@link kDDICT_ENTRY_DUPS} entry
	 * in the data dictionary, in which case it will return the
	 * {@link kSTATUS_DUPLICATE_ENTRIES} code, or {@link kSTATUS_CHECKED_DUPS} code if there
	 * are no duplicate entries.
	 *
	 * When writing duplicate entries it will fill the data dictionary
	 * {@link kDDICT_ENTRY_DUPS} entry as follows:
	 *
	 * <ul>
	 *	<li><tt>{@link kTYPE_DUPLICATES_CLUSTER}</tt>: It contains an array having as key
	 *		the column offset and as value the column value of the location, team, cluster,
	 *		identifier and, if relevant, the household and/or the mother identifiers.
	 *	<li><tt>{@link kTYPE_DUPLICATES_ROWS}</tt>: It contains an array holding the list
	 *		of rows where the duplicates can be found.
	 * </ul>
	 *
	 * The array key of the {@link kDDICT_ENTRY_DUPS} entry represents the duplicate
	 * groups identifier.
	 *
	 * @return int					Status code.
	 *
	 * @uses checkDatasetDuplicateEntries()
	 */
	public function CheckHouseholdDatasetDuplicates()
	{
		return $this->checkDatasetDuplicateEntries( self::kDDICT_HOUSEHOLD_ID );	// ==>

	} // CheckHouseholdDatasetDuplicates.


	/*===================================================================================
	 *	CheckChildDatasetRelatedMothers													*
	 *==================================================================================*/

	/**
	 * <h4>Check child dataset related mothers.</h4>
	 *
	 * This method can be used to identify invalid mother references in the child dataset,
	 * the method will load the {@link kDDICT_INVALID_MOTHERS} with an array structured as
	 * follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding the
	 * 		list of identifiers for the mother reference: the location, team, cluster,
	 * 		household and mother numbers.
	 * 	<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding the list of
	 * 		rows featuring the invalid reference rows.
	 * </ul>
	 *
	 * The method will return the {@link kSTATUS_CHECKED_REFS} status code if no invalid
	 * references were found, or the {@link kSTATUS_INVALID_REFERENCES} status code if
	 * invalid references were found.
	 *
	 * @return int					Status code.
	 *
	 * @uses checkDatasetInvalidMothers()
	 */
	public function CheckChildDatasetRelatedMothers()
	{
		return $this->checkDatasetInvalidMothers( self::kDDICT_CHILD_ID );			// ==>

	} // CheckChildDatasetRelatedMothers.


	/*===================================================================================
	 *	CheckChildDatasetRelatedHouseholds												*
	 *==================================================================================*/

	/**
	 * <h4>Check child dataset related households.</h4>
	 *
	 * This method can be used to identify invalid household references in the child
	 * dataset, the method will load the {@link kDDICT_INVALID_HOUSEHOLDS} with an array
	 * structured as follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding the
	 * 		list of identifiers for the household reference: the location, team, cluster and
	 * 		the household number.
	 * 	<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding the list of
	 * 		rows featuring the invalid references.
	 * </ul>
	 *
	 * The method will return the {@link kSTATUS_CHECKED_REFS} status code if no invalid
	 * references were found, or the {@link kSTATUS_INVALID_REFERENCES} status code if
	 * invalid references were found.
	 *
	 * @return int					Status code.
	 *
	 * @uses checkDatasetInvalidHouseholds()
	 */
	public function CheckChildDatasetRelatedHouseholds()
	{
		return $this->checkDatasetInvalidHouseholds( self::kDDICT_CHILD_ID );		// ==>

	} // CheckChildDatasetRelatedHouseholds.


	/*===================================================================================
	 *	CheckMotherDatasetRelatedHouseholds												*
	 *==================================================================================*/

	/**
	 * <h4>Check mother dataset related households.</h4>
	 *
	 * This method can be used to identify invalid household references in the mother
	 * dataset, the method will load the {@link kDDICT_INVALID_HOUSEHOLDS} with an array
	 * structured as follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding the
	 * 		list of identifiers for the household reference: the location, team, cluster and
	 * 		the household number.
	 * 	<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding the list of
	 * 		rows featuring the invalid references.
	 * </ul>
	 *
	 * The method will return the {@link kSTATUS_CHECKED_REFS} status code if no invalid
	 * references were found, or the {@link kSTATUS_INVALID_REFERENCES} status code if
	 * invalid references were found.
	 *
	 * @return int					Status code.
	 *
	 * @uses checkDatasetInvalidHouseholds()
	 */
	public function CheckMotherDatasetRelatedHouseholds()
	{
		return $this->checkDatasetInvalidHouseholds( self::kDDICT_MOTHER_ID );		// ==>

	} // CheckMotherDatasetRelatedHouseholds.


	/*===================================================================================
	 *	SetChildCount																	*
	 *==================================================================================*/

	/**
	 * <h4>Set children count.</h4>
	 *
	 * This method is used by the public interface to load the children count in the mother
	 * dataset, the method will count how many children each mother has and write the
	 * result into the mother dataset under the {@link kCOLLECTION_OFFSET_CHILD_COUNT}
	 * field; the method relies on the {@link kCOLLECTION_OFFSET_MOTHER_ID} field in the
	 * child collection.
	 *
	 * The method will raise an exception if the child or mother datasets are missing.
	 *
	 * @throws RuntimeException
	 *
	 * @uses datasetCollection()
	 * @uses datasetStatus()
	 */
	public function SetChildCount()
	{
		//
		// Check mother collection.
		//
		if( $this->datasetCollection( self::kDDICT_MOTHER_ID )->count() )
		{
			//
			// Check child collection.
			//
			if( $this->datasetCollection( self::kDDICT_CHILD_ID )->count() )
			{
				//
				// Iterate mothers.
				//
				$cursor = $this->datasetCollection( self::kDDICT_MOTHER_ID )->find();
				foreach( $cursor as $document )
					$this->datasetCollection( self::kDDICT_MOTHER_ID )
						->updateOne(
							[ '_id' => $document[ '_id' ] ],
							[ '$set' => [ self::kCOLLECTION_OFFSET_CHILD_COUNT
							=> $this->datasetCollection( self::kDDICT_CHILD_ID )
									->count( [
										self::kCOLLECTION_OFFSET_MOTHER_ID
										=> $document[ '_id' ]
									] ) ] ]
						);

				//
				// Set status.
				//
				$this->datasetStatus(
					self::kDDICT_MOTHER_ID,
					TRUE,
					self::kSTATUS_LOADED_STATS
				);

			} // Has child collection.

			//
			// Missing child collection.
			//
			else
				throw new RuntimeException(
					"Child collection not yet loaded." );						// !@! ==>

		} // Has mother collection.

		//
		// Missing mother collection.
		//
		else
			throw new RuntimeException(
				"Mother collection not yet loaded." );							// !@! ==>

	} // SetChildCount.


	/*===================================================================================
	 *	SetMotherCount																	*
	 *==================================================================================*/

	/**
	 * <h4>Set mothers count.</h4>
	 *
	 * This method is used by the public interface to load the children count in the mother
	 * dataset, the method will count how many children each mother has and write the
	 * result into the mother dataset under the {@link kCOLLECTION_OFFSET_CHILD_COUNT}
	 * field; the method relies on the {@link kCOLLECTION_OFFSET_MOTHER_ID} field in the
	 * child collection.
	 *
	 * The method will raise an exception if the child or mother datasets are missing.
	 *
	 * @throws RuntimeException
	 *
	 * @uses datasetCollection()
	 * @uses datasetStatus()
	 */
	public function SetMotherCount()
	{
		//
		// Check household collection.
		//
		if( $this->datasetCollection( self::kDDICT_HOUSEHOLD_ID )->count() )
		{
			//
			// Check mother collection.
			//
			if( $this->datasetCollection( self::kDDICT_MOTHER_ID )->count() )
			{
				//
				// Iterate households.
				//
				$cursor = $this->datasetCollection( self::kDDICT_HOUSEHOLD_ID )->find();
				foreach( $cursor as $document )
					$this->datasetCollection( self::kDDICT_HOUSEHOLD_ID )
						->updateOne(
							[ '_id' => $document[ '_id' ] ],
							[ '$set' => [ self::kCOLLECTION_OFFSET_MOTHER_COUNT
								=> $this->datasetCollection( self::kDDICT_MOTHER_ID )
									->count( [
										self::kCOLLECTION_OFFSET_HOUSEHOLD_ID
										=> $document[ '_id' ]
									] ) ] ]
						);

				//
				// Set status.
				//
				$this->datasetStatus(
					self::kDDICT_HOUSEHOLD_ID,
					TRUE,
					self::kSTATUS_LOADED_STATS
				);

			} // Has mother collection.

			//
			// Missing mother collection.
			//
			else
				throw new RuntimeException(
					"Mother collection not yet loaded." );						// !@! ==>

		} // Has household collection.

		//
		// Missing household collection.
		//
		else
			throw new RuntimeException(
				"Household collection not yet loaded." );						// !@! ==>

	} // SetMotherCount.



/*=======================================================================================
 *																						*
 *								PUBLIC MERGE INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	MergeSurvey																		*
	 *==================================================================================*/

	/**
	 * <h4>Merge survey.</h4>
	 *
	 * This method will merge the child, mother and household surveys into a single
	 * dataset. The method expects all tests to have passed, or an exception will be raised.
	 *
	 * @throws RuntimeException
	 *
	 * @uses ChildDatasetStatus()
	 * @uses MotherDatasetStatus()
	 * @uses HouseholdDatasetStatus()
	 * @uses datasetCollection()
	 * @uses getChildFields()
	 * @uses getMotherFields()
	 * @uses getHouseholdFields()
	 * @uses ChildDatasetDateOffset()
	 * @uses ChildDatasetLocationOffset()
	 * @uses ChildDatasetTeamOffset()
	 * @uses ChildDatasetClusterOffset()
	 * @uses ChildDatasetHouseholdOffset()
	 * @uses ChildDatasetMotherOffset()
	 * @uses ChildDatasetIdentifierOffset()
	 */
	public function MergeSurvey()
	{
		//
		// Check child status.
		//
		$status = $this->ChildDatasetStatus();
		if( ! ($status & self::kSTATUS_CHECKED_REFS ) )
			throw new RuntimeException(
				"Must run all checks on child dataset before merging." );		// !@! ==>
		if( $status & self::kSTATUS_DUPLICATE_COLUMNS )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are duplicate columns in child dataset." );				// !@! ==>
		if( $status & self::kSTATUS_DUPLICATE_ENTRIES )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are duplicate entries in child dataset." );				// !@! ==>
		if( $status & self::kSTATUS_INVALID_REFERENCES )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are invalid references in child dataset." );				// !@! ==>

		//
		// Check mother status.
		//
		$status = $this->MotherDatasetStatus();
		if( ! ($status & self::kSTATUS_CHECKED_REFS ) )
			throw new RuntimeException(
				"Must run all checks on mother dataset before merging." );		// !@! ==>
		if( $status & self::kSTATUS_DUPLICATE_COLUMNS )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are duplicate columns in mother dataset." );				// !@! ==>
		if( $status & self::kSTATUS_DUPLICATE_ENTRIES )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are duplicate entries in mother dataset." );				// !@! ==>
		if( $status & self::kSTATUS_INVALID_REFERENCES )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are invalid references in mother dataset." );			// !@! ==>

		//
		// Check household status.
		//
		$status = $this->HouseholdDatasetStatus();
		if( ! ($status & self::kSTATUS_CHECKED_DUPS ) )
			throw new RuntimeException(
				"Must run all checks on household dataset before merging." );	// !@! ==>
		if( $status & self::kSTATUS_DUPLICATE_COLUMNS )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are duplicate columns in household dataset." );			// !@! ==>
		if( $status & self::kSTATUS_DUPLICATE_ENTRIES )
			throw new RuntimeException(
				"Cannot merge: " .
				"there are duplicate entries in household dataset." );			// !@! ==>

		//
		// Check child dataset.
		//
		if( $this->datasetCollection( self::kDDICT_CHILD_ID )->count() )
		{
			//
			// Check mother dataset.
			//
			if( $this->datasetCollection( self::kDDICT_MOTHER_ID )->count() )
			{
				//
				// Check household dataset.
				//
				if( $this->datasetCollection( self::kDDICT_HOUSEHOLD_ID )->count() )
				{
					//
					// Clear survey dataset.
					//
					$this->mSurvey->drop();

					//
					// Select default fields.
					//
					$child_fields = $this->getChildFields();
					$mother_fields = $this->getMotherFields();
					$household_fields = $this->getHouseholdFields();

					//
					// Iterate children.
					//
					$cursor = $this->datasetCollection( self::kDDICT_CHILD_ID )->find();
					foreach( $cursor as $child )
					{
						//
						// Init document.
						//
						$document = [ '_id' => $child[ '_id' ] ];

						//
						// Set default group identifiers.
						//
						$document[ self::kCOLLECTION_OFFSET_DATE ]
							= $child[ $this->ChildDatasetDateOffset() ];
						$document[ self::kCOLLECTION_OFFSET_LOCATION ]
							= $child[ $this->ChildDatasetLocationOffset() ];
						$document[ self::kCOLLECTION_OFFSET_TEAM ]
							= $child[ $this->ChildDatasetTeamOffset() ];
						$document[ self::kCOLLECTION_OFFSET_CLUSTER ]
							= $child[ $this->ChildDatasetClusterOffset() ];
						$document[ self::kCOLLECTION_OFFSET_HOUSEHOLD ]
							= $child[ $this->ChildDatasetHouseholdOffset() ];
						$document[ self::kCOLLECTION_OFFSET_MOTHER ]
							= $child[ $this->ChildDatasetMotherOffset() ];
						$document[ self::kCOLLECTION_OFFSET_IDENTIFIER ]
							= $child[ $this->ChildDatasetIdentifierOffset() ];

						//
						// Load child fields.
						//
						foreach( $child_fields as $field )
						{
							if( array_key_exists( $field, $child ) )
								$document[ $field ] = $child[ $field ];
						}

						//
						// Set mother ID.
						//
						$document[ self::kCOLLECTION_OFFSET_MOTHER_ID ]
							= $child[ self::kCOLLECTION_OFFSET_MOTHER_ID ];

						//
						// Get mother.
						//
						$mother =
							$this->datasetCollection( self::kDDICT_MOTHER_ID )
								->findOne( [
									'_id' => $child[ self::kCOLLECTION_OFFSET_MOTHER_ID ]
								] );

						//
						// Load mother fields.
						//
						foreach( $mother_fields as $field )
						{
							if( array_key_exists( $field, $mother ) )
								$document[ $field ] = $mother[ $field ];
						}

						//
						// Set household ID.
						//
						$document[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ]
							= $child[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ];

						//
						// Get household.
						//
						$household =
							$this->datasetCollection( self::kDDICT_HOUSEHOLD_ID )
								->findOne( [
									'_id' => $child[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ]
								] );

						//
						// Load household fields.
						//
						foreach( $household_fields as $field )
						{
							if( array_key_exists( $field, $household ) )
								$document[ $field ] = $household[ $field ];
						}

						//
						// Save document.
						//
						$this->mSurvey->insertOne( $document );

					} // Iterating children.

					//
					// Set status.
					//
					$datasets = [
						self::kDDICT_CHILD_ID,
						self::kDDICT_MOTHER_ID,
						self::kDDICT_HOUSEHOLD_ID
					];
					foreach( $datasets as $dataset )
						$this->datasetStatus(
							$dataset,
							NULL,
							self::kSTATUS_VALID
						);

				} // Has households.

				else
					throw new RuntimeException(
						"Cannot merge: " .
						"empty household dataset." );							// !@! ==>

			} // Has mothers.

			else
				throw new RuntimeException(
					"Cannot merge: " .
					"empty mother dataset." );									// !@! ==>

		} // Has children.

		else
			throw new RuntimeException(
				"Cannot merge: " .
				"empty children dataset." );									// !@! ==>

	} // MergeSurvey.



/*=======================================================================================
 *																						*
 *								PUBLIC EXPORT INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ExportSurvey																	*
	 *==================================================================================*/

	/**
	 * <h4>Export survey.</h4>
	 *
	 * This method can be used to export the survey as an Excel file, it expects two
	 * parameters:
	 *
	 * <ul>
	 * 	<li><b>$thePath</b>: Export file path.
	 * 	<li><b>$theDataset</b>: Dataset selector; being a bitfield you may provide more than
	 * 		one choice, each dataset will be stored in its own worksheet:
	 *	 <ul>
	 *	 	<li><tt>{@link self::kDATASET_SELECTOR_CHILD}</tt>: Child dataset.
	 *	 	<li><tt>{@link self::kDATASET_SELECTOR_MOTHER}</tt>: Mother dataset.
	 *	 	<li><tt>{@link self::kDATASET_SELECTOR_HOUSEHOLD}</tt>: Household dataset.
	 *	 	<li><tt>{@link self::kDATASET_SELECTOR_MERGED}</tt>: Merged dataset.
	 *	 </ul>
	 * </ul>
	 *
	 * The method will consider datasets only if they have the {@link kSTATUS_VALID} status,
	 * the merged dataset will be considered only if it is not empty.
	 *
	 * @param string				$thePath			File path.
	 * @param int					$theDataset			Dataset selector.
	 * @throws RuntimeException
	 *
	 * @uses ChildDatasetStatus()
	 * @uses MotherDatasetStatus()
	 * @uses HouseholdDatasetStatus()
	 * @uses exportDataset()
	 * @uses exportMerged()
	 */
	public function ExportSurvey( string $thePath, int $theDataset )
	{
		//
		// Init local storage.
		//
		$worksheet_index = 0;

		//
		// Cache cells.
		//
		$cache_method = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
		if ( ! PHPExcel_Settings::setCacheStorageMethod( $cache_method ) )
			throw new RuntimeException(
				"Cannot export file: " .
				"cache method [$cache_method] not available." );				// !@! ==>

		//
		// Create Excel object.
		//
		$excel = new PHPExcel();

		//
		// Delete default worksheet.
		//
		$excel->removeSheetByIndex( 0 );

		//
		// Handle household dataset.
		//
		if( $theDataset & self::kDATASET_SELECTOR_HOUSEHOLD )
		{
			//
			// Check dataset status.
			//
			if( $this->HouseholdDatasetStatus() == self::kSTATUS_VALID )
			{
				//
				// Create household worksheet.
				//
				$worksheet = new PHPExcel_Worksheet( $excel, 'Households' );
				$excel->addSheet( $worksheet, $worksheet_index++ );

				//
				// Export to worksheet.
				//
				$this->exportDataset( $worksheet, self::kDDICT_HOUSEHOLD_ID );

			} // Valid status.

		} // Requested household dataset.

		//
		// Handle mother dataset.
		//
		if( $theDataset & self::kDATASET_SELECTOR_MOTHER )
		{
			//
			// Check dataset status.
			//
			if( $this->MotherDatasetStatus() == self::kSTATUS_VALID )
			{
				//
				// Create mother worksheet.
				//
				$worksheet = new PHPExcel_Worksheet( $excel, 'Mothers' );
				$excel->addSheet( $worksheet, $worksheet_index++ );

				//
				// Export to worksheet.
				//
				$this->exportDataset( $worksheet, self::kDDICT_MOTHER_ID );

			} // Valid status.

		} // Requested mother dataset.

		//
		// Handle child dataset.
		//
		if( $theDataset & self::kDATASET_SELECTOR_CHILD )
		{
			//
			// Check dataset status.
			//
			if( $this->ChildDatasetStatus() == self::kSTATUS_VALID )
			{
				//
				// Create child worksheet.
				//
				$worksheet = new PHPExcel_Worksheet( $excel, 'Children' );
				$excel->addSheet( $worksheet, $worksheet_index++ );

				//
				// Export to worksheet.
				//
				$this->exportDataset( $worksheet, self::kDDICT_CHILD_ID );

			} // Valid status.

		} // Requested child dataset.

		//
		// Handle merged dataset.
		//
		if( $theDataset & self::kDATASET_SELECTOR_MERGED )
		{
			//
			// Skip if empty.
			//
			if( $this->mSurvey->count() )
			{
				//
				// Create merged worksheet.
				//
				$worksheet = new PHPExcel_Worksheet( $excel, 'Merged' );
				$excel->addSheet( $worksheet, $worksheet_index++ );

				//
				// Export collection.
				//
				$this->exportMerged( $worksheet );

			} // Dataset not empty.

		} // Requested merged dataset.

		//
		// Write file.
		//
		$writer = PHPExcel_IOFactory::createWriter( $excel, 'Excel2007' );
		$writer->save( $thePath );

	} // ExportSurvey.



/*=======================================================================================
 *																						*
 *								PUBLIC STATISTICAL INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ChildClusterByTeam																*
	 *==================================================================================*/

	/**
	 * <h4>Return child cluster x team table.</h4>
	 *
	 * This method can be used to get a matrix of clusters by teams for the child
	 * dataset.
	 *
	 * @return array				Cluster and Team matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function ChildClusterByTeam()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_CHILD_ID,
				'Clusters',
				self::kDATASET_OFFSET_CLUSTER,
				self::kDATASET_OFFSET_TEAM
			);																		// ==>

	} // ChildClusterByTeam.


	/*===================================================================================
	 *	MotherClusterByTeam																*
	 *==================================================================================*/

	/**
	 * <h4>Return mother cluster x team table.</h4>
	 *
	 * This method can be used to get a matrix of clusters by teams for the mother
	 * dataset.
	 *
	 * @return array				Cluster and Team matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function MotherClusterByTeam()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_MOTHER_ID,
				'Clusters',
				self::kDATASET_OFFSET_CLUSTER,
				self::kDATASET_OFFSET_TEAM
			);																		// ==>

	} // MotherClusterByTeam.


	/*===================================================================================
	 *	HouseholdClusterByTeam																*
	 *==================================================================================*/

	/**
	 * <h4>Return household cluster x team table.</h4>
	 *
	 * This method can be used to get a matrix of clusters by teams for the household
	 * dataset.
	 *
	 * @return array				Cluster and Team matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function HouseholdClusterByTeam()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_HOUSEHOLD_ID,
				'Clusters',
				self::kDATASET_OFFSET_CLUSTER,
				self::kDATASET_OFFSET_TEAM
			);																		// ==>

	} // HouseholdClusterByTeam.


	/*===================================================================================
	 *	ChildClusterByLocation															*
	 *==================================================================================*/

	/**
	 * <h4>Return child cluster x location table.</h4>
	 *
	 * This method can be used to get a matrix of clusters by locations for the child
	 * dataset.
	 *
	 * @return array				Cluster and Location matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function ChildClusterByLocation()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_CHILD_ID,
				'Clusters',
				self::kDATASET_OFFSET_CLUSTER,
				self::kDATASET_OFFSET_LOCATION
			);																		// ==>

	} // ChildClusterByLocation.


	/*===================================================================================
	 *	MotherClusterByLocation															*
	 *==================================================================================*/

	/**
	 * <h4>Return mother cluster x location table.</h4>
	 *
	 * This method can be used to get a matrix of clusters by locations for the mother
	 * dataset.
	 *
	 * @return array				Cluster and Location matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function MotherClusterByLocation()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_MOTHER_ID,
				'Clusters',
				self::kDATASET_OFFSET_CLUSTER,
				self::kDATASET_OFFSET_LOCATION
			);																		// ==>

	} // MotherClusterByLocation.


	/*===================================================================================
	 *	HouseholdClusterByLocation														*
	 *==================================================================================*/

	/**
	 * <h4>Return household cluster x location table.</h4>
	 *
	 * This method can be used to get a matrix of clusters by locations for the household
	 * dataset.
	 *
	 * @return array				Cluster and Location matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function HouseholdClusterByLocation()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_HOUSEHOLD_ID,
				'Clusters',
				self::kDATASET_OFFSET_CLUSTER,
				self::kDATASET_OFFSET_LOCATION
			);																		// ==>

	} // HouseholdClusterByLocation.


	/*===================================================================================
	 *	ChildTeamByLocation																*
	 *==================================================================================*/

	/**
	 * <h4>Return child team x location table.</h4>
	 *
	 * This method can be used to get a matrix of teams by locations for the child
	 * dataset.
	 *
	 * @return array				Team and Location matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function ChildTeamByLocation()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_CHILD_ID,
				'Teams',
				self::kDATASET_OFFSET_TEAM,
				self::kDATASET_OFFSET_LOCATION
			);																		// ==>

	} // ChildTeamByLocation.


	/*===================================================================================
	 *	MotherTeamByLocation															*
	 *==================================================================================*/

	/**
	 * <h4>Return mother team x location table.</h4>
	 *
	 * This method can be used to get a matrix of teams by locations for the mother
	 * dataset.
	 *
	 * @return array				Team and Location matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function MotherTeamByLocation()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_MOTHER_ID,
				'Teams',
				self::kDATASET_OFFSET_TEAM,
				self::kDATASET_OFFSET_LOCATION
			);																		// ==>

	} // MotherTeamByLocation.


	/*===================================================================================
	 *	HouseholdTeamByLocation															*
	 *==================================================================================*/

	/**
	 * <h4>Return household team x location table.</h4>
	 *
	 * This method can be used to get a matrix of teams by locations for the household
	 * dataset.
	 *
	 * @return array				Team and Location matrix.
	 *
	 * @uses getMatrixTable()
	 */
	public function HouseholdTeamByLocation()
	{
		return
			$this->getMatrixTable(
				self::kDDICT_HOUSEHOLD_ID,
				'Teams',
				self::kDATASET_OFFSET_TEAM,
				self::kDATASET_OFFSET_LOCATION
			);																		// ==>

	} // HouseholdTeamByLocation.



/*=======================================================================================
 *																						*
 *									STATIC UTILITIES									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Excel2Date																		*
	 *==================================================================================*/

	/**
	 * <h4>Convert an Excel date to string.</h4>
	 *
	 * This method will convert the provided integer into a string date. The method expects
	 * an integer value corresponding to an Excel date and will return the date in
	 * <tt>YYYY-MM-DD</tt> string format.
	 *
	 * @param int					$theDate			Date in Excel format.
	 * @return string				String date in <tt>YYYY-MM-DD</tt> format.
	 */
	static function Excel2Date( int $theDate )
	{
		//
		// Init base date.
		//
		$tmp1 = new DateTime( '1900-01-01' );

		//
		// Add interval.
		//
		$tmp2 = new DateInterval( 'P' . $theDate . 'D' );
		$tmp1->add( $tmp2 );

		return $tmp1->format( 'Y-m-d' );											// ==>

	} // Excel2Date.


	/*===================================================================================
	 *	Date2Excel																		*
	 *==================================================================================*/

	/**
	 * <h4>Convert a string date to an Excel date.</h4>
	 *
	 * This method will convert the provided string date into an Excel date. The method
	 * expects a date as a string in <tt>YYYY-MM-DD</tt> format and will return an integer
	 * corresponding to the Excel date.
	 *
	 * @param string				$theDate			Date in <tt>YYYY-MM-DD</tt> format.
	 * @return int					Date in Excel format.
	 */
	static function Date2Excel( string $theDate )
	{
		//
		// Init dates.
		//
		$tmp1 = new DateTime( '1900-01-01' );
		$tmp2 = new DateTime( $theDate );

		//
		// Get interval.
		//
		$interval = $tmp1->diff( $tmp2 );

		return (int)$interval->format( '%d' );										// ==>

	} // Date2Excel.



/*=======================================================================================
 *																						*
 *							PROTECTED INITIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	newDataDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Initialise household data dictionary.</h4>
	 *
	 * This method can be used to retrieve a new data dictionary, the method will return the
	 * <em>idle</em> version of the data dictionary record.
	 *
	 * The method expects the value of the record ID (<tt>_id</tt>).
	 *
	 * @param string				$theIdentifier		Record ID.
	 * @return array				New data dictionary record.
	 */
	protected function newDataDictionary( string $theIdentifier )
	{
		//
		// Init common fields.
		//
		$document = [
			'_id'							=> $theIdentifier,
			self::kDDICT_STATUS				=> self::kSTATUS_IDLE,
			self::kDDICT_FIELDS				=> [],
			self::kDDICT_COLUMNS			=> [],
			self::kDDICT_COLUMN_DUPS		=> [],
			self::kDDICT_ENTRY_DUPS			=> [],
		];

		//
		// Add related fields.
		//
		switch( $theIdentifier )
		{
			case self::kDDICT_CHILD_ID:
				$document[ self::kDDICT_INVALID_MOTHERS ] = [];

			case self::kDDICT_MOTHER_ID:
				$document[ self::kDDICT_INVALID_HOUSEHOLDS ] = [];
				break;
		}

		return $document;															// ==>

	} // newDataDictionary.



/*=======================================================================================
 *																						*
 *						PROTECTED DICTIONARY MEMBER ACCESSOR INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	datasetStatus																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset status.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset status
	 * code, the method expects three parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theOperation</b>: Status operation. Since the status is a bitfield, the
	 * 		provided value may:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Set the status with the provided value (<tt>=</tt>).
	 * 		<li><tt>TRUE<tt>: Add the value to the existing status (<tt>|=</tt>).
	 * 		<li><tt>FALSE<tt>: Remove the value from the existing status (<tt>\&= ~</tt>).
	 * 	 </ul>
	 * 		By default the value is <tt>NULL</tt>, so that retrieving the status needs only
	 * 		the dataset selector.
	 * 	<li><b>$theValue</b>: Status or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current status; <em>in this case the previous parameter
	 * 			is ignored</em>.
	 * 		<li><i>int<i>: Set, add or reset status (depending on the previous parameter).
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return the current status, or raise an exception if the the selector
	 * is not correct.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param mixed					$theOperation		Bitfield operation.
	 * @param string				$theValue			Dataset variable name.
	 * @return int					Current status.
	 * @throws InvalidArgumentException
	 */
	protected function datasetStatus( string $theDataset,
									  $theOperation = NULL,
									  $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
			return
				$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ];			// ==>

		//
		// Set new status.
		//
		if( $theOperation === NULL )
			$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ]
				= $theValue;

		//
		// Add status.
		//
		elseif( $theOperation === TRUE )
			$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ]
				|= $theValue;

		//
		// Reset status.
		//
		elseif( $theOperation === FALSE )
			$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ]
				&= (~ $theValue);

		//
		// Invalid operation.
		//
		else
			throw new InvalidArgumentException(
				"Invalid bitfield operation." );								// !@! ==>

		return $this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ];				// ==>

	} // datasetStatus.


	/*===================================================================================
	 *	datasetCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset status.</h4>
	 *
	 * This method is used by the public interface to retrieve the dataset final collection,
	 * the method expects a single parameter that represents the dataset selector:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return the the relative collection, or raise an exception if the
	 * provided selector is invalid.
	 * is not correct.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return Collection			Dataset final collection.
	 * @throws InvalidArgumentException
	 */
	protected function datasetCollection( string $theDataset )
	{
		//
		// Parse dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
				return $this->mChild;												// ==>

			case self::kDDICT_MOTHER_ID:
				return $this->mMother;												// ==>

			case self::kDDICT_HOUSEHOLD_ID:
				return $this->mHousehold;											// ==>

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

	} // datasetCollection.


	/*===================================================================================
	 *	datasetPath																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset file path.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset file path
	 * from the data dictionary, the method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Dataset file path or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>string<i>: New dataset path.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct, the file is
	 * not readable or a directory; when retrieving the current value and the dataset was
	 * not yet declared, the method will return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param string				$theValue			Dataset file path or operation.
	 * @return string				Dataset file path.
	 * @throws InvalidArgumentException
	 */
	protected function datasetPath( string $theDataset, $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists(
				self::kDATASET_OFFSET_FILE, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new file.
		//
		else
		{
			//
			// Open file in read.
			//
			$file = new SplFileObject( (string)$theValue, "r" );

			//
			// Check file.
			//
			if( (! $file->isFile())
			 || (! $file->isWritable()) )
				throw new InvalidArgumentException(
					"Invalid file reference [$theValue]." );					// !@! ==>

			//
			// Set dictionary entry.
			//
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_FILE ] = $file->getRealPath();

		} // Set new value.

		return
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_FILE ];										// ==>

	} // datasetPath.


	/*===================================================================================
	 *	datasetHeaderRow																*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset header row.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset header
	 * row number from the data dictionary, the method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Dataset header row or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>int<i>: New dataset header row.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct and the value
	 * is not an integer; when retrieving the current value and the dataset was not yet
	 * declared, the method will return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param int					$theValue			Dataset header line or operation.
	 * @return int					Dataset header line number.
	 * @throws InvalidArgumentException
	 */
	protected function datasetHeaderRow( string $theDataset, $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists(
				self::kDATASET_OFFSET_HEADER, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new header line.
		//
		else
		{
			//
			// Check value.
			//
			if( ! is_int( $theValue ) )
				throw new InvalidArgumentException(
					"Invalid header row number [$theValue]." );					// !@! ==>

			//
			// Set dictionary entry.
			//
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_HEADER ] = (int)$theValue;

		} // Set new value.

		return
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_HEADER ];									// ==>

	} // datasetHeaderRow.


	/*===================================================================================
	 *	datasetDataRow																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset data row.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset data
	 * row number from the data dictionary, the method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Dataset data row or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>int<i>: New dataset data row.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct and the value
	 * is not an integer; when retrieving the current value and the dataset was not yet
	 * declared, the method will return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param int					$theValue			Dataset data line or operation.
	 * @return int					Dataset header line number.
	 * @throws InvalidArgumentException
	 */
	protected function datasetDataRow( string $theDataset, $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists(
				self::kDATASET_OFFSET_DATA, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new data line.
		//
		else
		{
			//
			// Check value.
			//
			if( ! is_int( $theValue ) )
				throw new InvalidArgumentException(
					"Invalid first data row number [$theValue]." );				// !@! ==>

			//
			// Set dictionary entry.
			//
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_DATA ] = (int)$theValue;

		} // Set new value.

		return
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_DATA ];										// ==>

	} // datasetDataRow.


	/*===================================================================================
	 *	datasetOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset offset.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the offset names of
	 * specific dataset columns, the method expects three parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theOffset</b>: Dictionary offset, this value corresponds to the data
	 * 		dictionary record offset to be managed:
	 * 	 <ul>
	 * 		<li><tt>{@link kDATASET_DATE}<tt>: Survey date.
	 * 		<li><tt>{@link kDATASET_LOCATION}<tt>: Survey location number.
	 * 		<li><tt>{@link kDATASET_TEAM}<tt>: Survey team number.
	 * 		<li><tt>{@link kDATASET_CLUSTER}<tt>: Survey cluster number.
	 * 		<li><tt>{@link kDATASET_HOUSEHOLD}<tt>: Survey household number.
	 * 		<li><tt>{@link kDATASET_MOTHER}<tt>: Survey mother number.
	 * 		<li><tt>{@link kDATASET_IDENTIFIER}<tt>: Survey unit number.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Offset or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>string<i>: New offset.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct; when
	 * retrieving the current value and the dataset was not yet declared, the method will
	 * return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param string				$theOffset			Dictionary offset.
	 * @param string				$theValue			Value or operation.
	 * @return string				Current value.
	 * @throws InvalidArgumentException
	 */
	protected function datasetOffset( string $theDataset,
									  string $theOffset,
									  string $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists( $theOffset, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new offset.
		//
		else
			$this->mDDICTInfo[ $theDataset ][ $theOffset ] = (string)$theValue;

		return $this->mDDICTInfo[ $theDataset ][ $theOffset ];						// ==>

	} // datasetOffset.


	/*===================================================================================
	 *	dictionaryList																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dictionary list.</h4>
	 *
	 * This method is used by the public interface to set or retrieve data dictionary list
	 * elements, the method expects three parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theOffset</b>: Dictionary offset, this value corresponds to the data
	 * 		dictionary record offset to be managed:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_COLUMNS}<tt>: Dataset header columns list.
	 * 		<li><tt>{@link kDDICT_FIELDS}<tt>: Data dictionary fields list.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Value or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>array<i>: New value.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct; when
	 * retrieving the current value and the dataset was not yet declared, the method will
	 * return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param string				$theOffset			Dictionary offset.
	 * @param array					$theValue			Value or operation.
	 * @return array				Current value.
	 * @throws InvalidArgumentException
	 */
	protected function dictionaryList( string $theDataset,
									   string $theOffset,
									    array $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists( $theOffset, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new offset.
		//
		else
		{
			//
			// Assert data type.
			//
			if( ! is_array( $theValue ) )
				throw new InvalidArgumentException(
					"Invalid columns list data type." );						// !@! ==>

			//
			// Set value in data dictionary.
			//
			$this->mDDICTInfo[ $theDataset ][ $theOffset ] = $theValue;

		} // New value.

		return $this->mDDICTInfo[ $theDataset ][ $theOffset ];						// ==>

	} // dictionaryList.



/*=======================================================================================
 *																						*
 *							PROTECTED PROCESSING UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	loadDataset																		*
	 *==================================================================================*/

	/**
	 * <h4>Load a dataset.</h4>
	 *
	 * This method is used by the public interface to load a dataset file, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset file was not yet declared and
	 * will set and return the {@link kSTATUS_LOADED} status code.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return int					Status code ({@link kSTATUS_LOADED}).
	 * @throws RuntimeException
	 *
	 * @uses datasetPath()
	 * @uses originalCollection()
	 * @uses dictionaryList()
	 * @uses datasetStatus()
	 */
	protected function loadDataset( string $theDataset )
	{
		//
		// Check dataset.
		//
		$path = $this->datasetPath( $theDataset );
		if( $path !== NULL )
		{
			//
			// Get original collection.
			//
			$collection = $this->originalCollection( $theDataset );
			$collection->drop();

			//
			// Load current worksheet.
			//
			$worksheet =
				PHPExcel_IOFactory::createReader(
					PHPExcel_IOFactory::identify( $path ) )
						->setReadDataOnly( TRUE )
						->load( $path )
						->getActiveSheet();

			//
			// Reset data dictionary.
			//
			switch( $theDataset )
			{
				case self::kDDICT_CHILD_ID:
					$this->dictionaryList( $theDataset, self::kDDICT_INVALID_MOTHERS, [] );

				case self::kDDICT_MOTHER_ID:
					$this->dictionaryList( $theDataset, self::kDDICT_INVALID_HOUSEHOLDS, [] );

				default:
					$this->datasetStatus( $theDataset, NULL, self::kSTATUS_IDLE );
					$this->dictionaryList( $theDataset, self::kDDICT_FIELDS, [] );
					$this->dictionaryList( $theDataset, self::kDDICT_COLUMNS, [] );
					$this->dictionaryList( $theDataset, self::kDDICT_COLUMN_DUPS, [] );
					$this->dictionaryList( $theDataset, self::kDDICT_ENTRY_DUPS, [] );
					break;
			}

			//
			// Iterate rows.
			//
			foreach( $worksheet->getRowIterator() as $row )
			{
				//
				// Init local storage.
				//
				$document = [ '_id' => $row->getRowIndex() ];

				//
				// Iterate columns.
				//
				foreach( $row->getCellIterator() as $cell )
					$document[ $cell->getColumn() ]
						= $cell->getValue();

				//
				// Save document.
				//
				$collection->insertOne( $document );

			} // Iterating rows.

			return
				$this->datasetStatus(
					$theDataset,
					NULL,
					self::kSTATUS_LOADED
				);																	// ==>

		} // Has dataset path.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset file not yet defined." );									// !@! ==>

	} // loadDataset.


	/*===================================================================================
	 *	loadDatasetHeader																*
	 *==================================================================================*/

	/**
	 * <h4>Load dataset header.</h4>
	 *
	 * This method is used by the public interface to load the dataset header, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset header row number was not yet
	 * declared and if the declared row cannot be found in the original collection.
	 *
	 * If the method encounters duplicate header values, the method will fill the header row
	 * in the data dictionary, but return the {@link kSTATUS_DUPLICATE_COLUMNS} status code.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return int					Status code ({@link kSTATUS_LOADED}).
	 * @throws RuntimeException
	 *
	 * @uses datasetHeaderRow()
	 * @uses originalCollection()
	 * @uses dictionaryList()
	 * @uses datasetStatus()
	 */
	protected function loadDatasetHeader( string $theDataset )
	{
		//
		// Check header row.
		//
		$row = $this->datasetHeaderRow( $theDataset );
		if( $row !== NULL )
		{
			//
			// Load header row.
			//
			$document =
				$this->originalCollection( $theDataset )
					->findOne( [ '_id' => $row ] );
			if( $document !== NULL )
			{
				//
				// Init local storage.
				//
				$header = $errors = [];

				//
				// Iterate row.
				//
				foreach( $document as $column => $value )
				{
					//
					// Skip row number.
					//
					if( $column == '_id' )
						continue;												// =>

					//
					// Skip empty values.
					//
					$value = trim( $value );
					if( strlen( $value ) )
					{
						//
						// Check header.
						//
						$index = array_search( $value, $header );
						if( $index !== FALSE )
						{
							//
							// Set value.
							//
							if( ! in_array( $value, $errors ) )
								$errors[] = $value;

						} // Found duplicate.

						//
						// Set header.
						//
						$header[ $column ] = $value;

					} // Not empty.

				} // Iterating header row.

				//
				// Reset data dictionary.
				//
				switch( $theDataset )
				{
					case self::kDDICT_CHILD_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_MOTHERS, [] );

					case self::kDDICT_MOTHER_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_HOUSEHOLDS, [] );
				}

				//
				// Load columns and errors.
				//
				$this->dictionaryList( $theDataset, self::kDDICT_COLUMNS, $header );
				$this->dictionaryList( $theDataset, self::kDDICT_COLUMN_DUPS, $errors );
				$this->dictionaryList( $theDataset, self::kDDICT_ENTRY_DUPS, [] );
				switch( $theDataset )
				{
					case self::kDDICT_CHILD_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_MOTHERS, [] );

					case self::kDDICT_MOTHER_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_HOUSEHOLDS, [] );
						break;
				}

				//
				// Handle duplicates.
				//
				if( count( $errors ) )
					$this->datasetStatus(
						$theDataset,
						TRUE,
						self::kSTATUS_DUPLICATE_COLUMNS
					);

				return $this->datasetStatus( $theDataset );							// ==>

			} // Found row.

			//
			// Missing header row.
			//
			throw new RuntimeException(
				"Missing dataset header row [$row]." );							// !@! ==>

		} // Has header row.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset header row not yet defined." );							// !@! ==>

	} // loadDatasetHeader.


	/*===================================================================================
	 *	loadDatasetFields																*
	 *==================================================================================*/

	/**
	 * <h4>Load dataset fields.</h4>
	 *
	 * This method is used by the public interface to load the dataset fields, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset columns were not yet loaded.
	 *
	 * The method will determine the data type of all columns and load the information in
	 * the {@link kDDICT_FIELDS} element of the data dictionary.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @throws RuntimeException
	 *
	 * @uses datasetDataRow()
	 * @uses originalCollection()
	 * @uses dictionaryList()
	 * @uses datasetOffset()
	 */
	protected function loadDatasetFields( string $theDataset )
	{
		//
		// Check data row.
		//
		$row = $this->datasetDataRow( $theDataset );
		if( $row !== NULL )
		{
			//
			// Check dataset columns.
			//
			$columns = $this->dictionaryList( $theDataset, self::kDDICT_COLUMNS );
			if( count( $columns  ) )
			{
				//
				// Init local storage.
				//
				$fields = [];
				$date = $this->datasetOffset( $theDataset, self::kDATASET_OFFSET_DATE );

				//
				// Iterate columns.
				//
				foreach( $columns as $column => $field )
				{
					//
					// Handle distinct values.
					//
					$values =
						$this->originalCollection( $theDataset )
							->distinct(
								$column,
								[ '_id' => [ '$gt' => $row ],
								  $column => [ '$ne' => NULL ] ]
							);
					if( count( $values ) )
					{
						//
						// Init local storage.
						//
						$count = 0;
						$fields[ $field ] = [];
						$kind = $type = self::kTYPE_INTEGER;

						//
						// Iterate distinct values.
						//
						foreach( $values as $value )
						{
							//
							// Skip empty values.
							//
							$value = trim( $value );
							if( strlen( $value ) )
							{
								//
								// Handle number.
								//
								if( is_numeric( $value ) )
								{
									//
									// Check decimal.
									//
									$tmp = explode( '.', $value );
									if( count( $tmp ) > 1 )
									{
										//
										// Set kind.
										//
										if( $kind == self::kTYPE_INTEGER )
											$kind = self::kTYPE_NUMBER;

										//
										// Check decimal.
										//
										if( $tmp[ 1 ] != '0' )
											$kind = $type = self::kTYPE_DOUBLE;

									} // Has decimals.

								} // Is numeric.

								//
								// Handle string.
								//
								else
								{
									//
									// Must be string.
									//
									$kind = $type = self::kTYPE_STRING;

									break;										// =>

								} // Value is string.

								//
								// Increment distinct values count.
								//
								$count++;

							} // Not empty.

						} // Iterating distinct values.

						//
						// Set kind, type and distinct count.
						//
						$fields[ $field ][ self::kFIELD_KIND ] = $kind;
						$fields[ $field ][ self::kFIELD_TYPE ] = ( $date == $field )
															   ? self::kTYPE_DATE
															   : $type;
						$fields[ $field ][ self::kFIELD_DISTINCT ] = $count;

					} // Column has values.

				} // Iterating columns.

				//
				// Update data dictionary.
				//
				$this->dictionaryList( $theDataset, self::kDDICT_FIELDS, $fields );
				$this->dictionaryList( $theDataset, self::kDDICT_ENTRY_DUPS, [] );
				switch( $theDataset )
				{
					case self::kDDICT_CHILD_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_MOTHERS, [] );

					case self::kDDICT_MOTHER_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_HOUSEHOLDS, [] );
						break;
				}

			} // Has columns.

			//
			// Missing columns.
			//
			else
				throw new RuntimeException(
					"Dataset header columns not yet loaded." );					// !@! ==>

		} // Declared data row.

		//
		// Missing data row.
		//
		else
			throw new RuntimeException(
				"Dataset data row not yet declared." );							// !@! ==>

	} // loadDatasetFields.


	/*===================================================================================
	 *	loadDatasetData																	*
	 *==================================================================================*/

	/**
	 * <h4>Load dataset data.</h4>
	 *
	 * This method is used by the public interface to load the final collection, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the original collection was not yet loaded.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @throws RuntimeException
	 *
	 * @uses originalCollection()
	 * @uses dictionaryList()
	 * @uses datasetDataRow()
	 * @uses datasetCollection()
	 */
	protected function loadDatasetData( string $theDataset )
	{
		//
		// Check dataset fields.
		//
		if( $this->originalCollection( $theDataset )->count() )
		{
			//
			// Check dataset columns.
			//
			$columns = $this->dictionaryList( $theDataset, self::kDDICT_COLUMNS );
			if( count( $columns ) )
			{
				//
				// Check data column.
				//
				$data_column = $this->datasetDataRow( $theDataset );
				if( $data_column !== NULL )
				{
					//
					// Get final collection.
					//
					$collection = $this->datasetCollection( $theDataset );
					$collection->drop();

					//
					// Iterate original collection.
					//
					$cursor = $this->originalCollection( $theDataset )->find();
					foreach( $cursor as $original )
					{
						//
						// Skip header rows.
						//
						if( $original[ '_id' ] < $data_column )
							continue;											// =>

						//
						// Init document.
						//
						$document = [ '_id' => $original[ '_id' ] ];

						//
						// Iterate fields.
						//
						foreach(
							$this->dictionaryList( $theDataset, self::kDDICT_FIELDS )
								as $field => $dict )
						{
							//
							// Get original column.
							//
							$index = array_search( $field, $columns );
							if( $index === FALSE )
								throw new RuntimeException(
									"Bug: " .
									"field [$field] not found in columns." );	// !@! ==>

							//
							// Skip empty fields.
							//
							$value = trim( $original[ $index ] );
							if( strlen( $value ) )
							{
								//
								// Cast value.
								//
								switch( $dict[ self::kFIELD_TYPE ] )
								{
									case self::kTYPE_STRING:
										$document[ $field ] = (string)$value;
										break;

									case self::kTYPE_INTEGER:
										$document[ $field ] = (int)$value;
										break;

									case self::kTYPE_NUMBER:
										$document[ $field ] = (float)$value;
										break;

									case self::kTYPE_DOUBLE:
										$document[ $field ] = (double)$value;
										break;

									case self::kTYPE_DATE:
										if( $dict[ self::kFIELD_KIND ]
											== self::kTYPE_INTEGER )
											$document[ $field ]
												= self::Excel2Date( $value );
										else
											$document[ $field ] = (string)$value;
										break;

								} // Parsing data type.

							} // Has value.

						} // Iterating fields.

						//
						// Write value.
						//
						$collection->insertOne( $document );

					} // Iterating original collection.

				} // Has data column.

				//
				// Missing data column.
				//
				else
					throw new RuntimeException(
						"Data column not yet declared." );						// !@! ==>

			} // Has columns.

			//
			// Missing columns.
			//
			else
				throw new RuntimeException(
					"Dataset header columns not yet loaded." );					// !@! ==>

		} // Declared data row.

		//
		// Missing original collection.
		//
		else
			throw new RuntimeException(
				"Original collection not yet loaded." );						// !@! ==>

	} // loadDatasetData.


	/*===================================================================================
	 *	checkDatasetDuplicateEntries													*
	 *==================================================================================*/

	/**
	 * <h4>Check for duplicate entries.</h4>
	 *
	 * This method is used by the public interface to check whether the dataset has
	 * duplicate entries, the method expects a single parameter that represents the dataset
	 * identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset columns were not yet loaded and
	 * if the location, team, cluster, identifier and, if relevant, the mother and/or
	 * household identifiers were not declared.
	 *
	 * The method will return the {@link kSTATUS_CHECKED_DUPS} status code if no duplicates
	 * were found, or the {@link kSTATUS_DUPLICATE_ENTRIES} status code if duplicates were
	 * found: in that case the method will fill the data dictionary
	 * {@link kDDICT_COLUMN_DUPS} entry as follows:
	 *
	 * <ul>
	 * 	<li><em>index</em>: The array index holds the duplicates group identifier.
	 * 	 <ul>
	 * 		<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding
	 * 			the list of identifiers for the group: the location, team, cluster and unit
	 * 			identifier, and, depending on the unit, the household and/or the mother
	 * 			identifier.
	 * 		<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding
	 * 			the list of duplicate rows for the current group.
	 * 	 </ul>
	 * </ul>
	 *
	 * The array key of the {@link kDDICT_COLUMN_DUPS} entry represents the duplicate
	 * groups identifier.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return int					Status code.
	 * @throws RuntimeException
	 *
	 * @uses dictionaryList()
	 * @uses datasetCollection()
	 * @uses datasetOffset()
	 * @uses datasetStatus()
	 */
	protected function checkDatasetDuplicateEntries( string $theDataset )
	{
		//
		// Check dataset columns.
		//
		$columns = $this->dictionaryList( $theDataset, self::kDDICT_COLUMNS );
		if( count( $columns  ) )
		{
			//
			// Check dataset collection.
			//
			if( $this->datasetCollection( $theDataset )->count() )
			{
				//
				// Init local storage.
				//
				$fields = [];

				//
				// Get location field.
				//
				if( ($tmp =
						$this->datasetOffset(
							$theDataset,
							self::kDATASET_OFFSET_LOCATION )) !== NULL )
					$fields[] = $tmp;
				else
					throw new RuntimeException(
						"Location field not yet declared." );					// !@! ==>

				//
				// Get team field.
				//
				if( ($tmp =
						$this->datasetOffset(
							$theDataset,
							self::kDATASET_OFFSET_TEAM )) !== NULL )
					$fields[] = $tmp;
				else
					throw new RuntimeException(
						"Team field not yet declared." );						// !@! ==>

				//
				// Get cluster field.
				//
				if( ($tmp =
						$this->datasetOffset(
							$theDataset,
							self::kDATASET_OFFSET_CLUSTER )) !== NULL )
					$fields[] = $tmp;
				else
					throw new RuntimeException(
						"Cluster field not yet declared." );					// !@! ==>

				//
				// Get household and mother fields.
				//
				switch( $theDataset )
				{
					case self::kDDICT_CHILD_ID:
						if( ($tmp =
								$this->datasetOffset(
									$theDataset,
									self::kDATASET_OFFSET_MOTHER )) !== NULL )
							$fields[] = $tmp;
						else
							throw new RuntimeException(
								"Mother field not yet declared." );				// !@! ==>

					case self::kDDICT_MOTHER_ID:
						if( ($tmp =
								$this->datasetOffset(
									$theDataset,
									self::kDATASET_OFFSET_HOUSEHOLD )) !== NULL )
							$fields[] = $tmp;
						else
							throw new RuntimeException(
								"Household field not yet declared." );			// !@! ==>
						break;

				} // Getting household and mother identifier fields.

				//
				// Get identifier field.
				//
				if( ($tmp =
						$this->datasetOffset(
							$theDataset,
							self::kDATASET_OFFSET_IDENTIFIER )) !== NULL )
					$fields[] = $tmp;
				else
					throw new RuntimeException(
						"Identifier field not yet declared." );					// !@! ==>

				//
				// Reset data dictionary.
				//
				$this->dictionaryList( $theDataset, self::kDDICT_ENTRY_DUPS, [] );
				switch( $theDataset )
				{
					case self::kDDICT_CHILD_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_MOTHERS, [] );

					case self::kDDICT_MOTHER_ID:
						$this->dictionaryList( $theDataset, self::kDDICT_INVALID_HOUSEHOLDS, [] );
						break;
				}

				//
				// Reset error status.
				//
				$this->datasetStatus(
					$theDataset,
					FALSE,
					self::kSTATUS_DUPLICATE_ENTRIES
				);

				//
				// Reset operation status.
				//
				$this->datasetStatus(
					$theDataset,
					FALSE,
					self::kSTATUS_CHECKED_DUPS
				);

				//
				// Init pipeline.
				//
				$pipeline = [];

				//
				// Init selection group.
				//
				$selection = [];
				foreach( $fields as $field )
					$selection[ $field ] = '$' . $field;

				//
				// Add group.
				//
				$pipeline[] = [
					'$group' => [ '_id' => $selection,
						'count' => [ '$sum' => 1 ] ]
				];

				//
				// Add duplicates match.
				//
				$pipeline[] = [
					'$match' => [ 'count' => [ '$gt' => 1 ] ]
				];

				//
				// Aggregate.
				//
				$duplicates =
					$this->datasetCollection( $theDataset )
						->aggregate( $pipeline, [ 'allowDiskUse' => TRUE ]
						);

				//
				// Iterate duplicate groups.
				//
				$dups = [];
				$duplicate_id = 1;
				foreach( $duplicates as $duplicate )
				{
					//
					// Get group.
					//
					$duplicate = $duplicate[ '_id' ]->getArrayCopy();
					if( count( $duplicate ) )
					{
						//
						// Init duplicates entry.
						//
						$dups[ $duplicate_id ] = [
							self::kGROUP_CLUSTER => $duplicate,
							self::kGROUP_ROWS => []
						];

						//
						// Locate duplicates.
						//
						$cursor =
							iterator_to_array(
								$this->datasetCollection( $theDataset )->find( $duplicate )
							);
						foreach( $cursor as $document )
							$dups[ $duplicate_id ][ self::kGROUP_ROWS ][]
								= $document[ '_id' ];

						//
						// Ingrement group identifier.
						//
						$duplicate_id++;

					} // Found duplicate.

				} // Iterating duplicates.

				//
				// Handle duplicates.
				//
				if( count( $dups ) )
				{
					//
					// Set status.
					//
					$this->datasetStatus(
						$theDataset,
						TRUE,
						self::kSTATUS_DUPLICATE_ENTRIES
					);

					//
					// Load duplicates in data dictionary.
					//
					$this->dictionaryList( $theDataset, self::kDDICT_ENTRY_DUPS, $dups );

				} // Has duplicates.

				//
				// Set status.
				//
				$this->datasetStatus(
					$theDataset,
					TRUE,
					self::kSTATUS_CHECKED_DUPS
				);

			} // Has columns.

			//
			// Missing dataset collection.
			//
			else
				throw new RuntimeException(
					"Dataset collection not yet loaded." );						// !@! ==>

		} // Has columns.

		//
		// Missing columns.
		//
		else
			throw new RuntimeException(
				"Dataset header columns not yet loaded." );						// !@! ==>

		return $this->datasetStatus( $theDataset );									// ==>

	} // checkDatasetDuplicateEntries.


	/*===================================================================================
	 *	checkDatasetInvalidMothers														*
	 *==================================================================================*/

	/**
	 * <h4>Check for invalid mother references.</h4>
	 *
	 * This method is used by the public interface to check whether the dataset has
	 * invalid mother references, the method expects a single parameter that represents the
	 * dataset identifier, by default it should be {@link kDDICT_CHILD_ID}.
	 *
	 * The method will raise an exception if the the mother dataset was not yet loaded and
	 * if the location, team, cluster, household and mother identifiers were not declared.
	 *
	 * The method will return the {@link kSTATUS_CHECKED_REFS} status code if no invalid
	 * references were found, or the {@link kSTATUS_INVALID_REFERENCES} status code if
	 * invalid references were found: in that case the method will fill the data dictionary
	 * {@link kDDICT_INVALID_MOTHERS} entry as follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding the
	 * 		list of identifiers for the mother reference: the location, team, cluster,
	 * 		household and mother numbers.
	 * 	<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding the list of
	 * 		rows featuring the invalid reference rows.
	 * </ul>
	 *
	 * The method will also load the {@link kCOLLECTION_OFFSET_MOTHER} mother key into the
	 * child collection.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return int					Status code.
	 * @throws RuntimeException
	 *
	 * @uses dictionaryList()
	 * @uses datasetCollection()
	 * @uses datasetOffset()
	 * @uses datasetStatus()
	 */
	protected function checkDatasetInvalidMothers( string $theDataset = self::kDDICT_CHILD_ID )
	{
		//
		// Check dataset fields.
		//
		$columns = $this->dictionaryList( $theDataset, self::kDDICT_FIELDS );
		if( count( $columns  ) )
		{
			//
			// Check dataset collection.
			//
			if( $this->datasetCollection( $theDataset )->count() )
			{
				//
				// Check mother collection.
				//
				if( $this->datasetCollection( self::kDDICT_MOTHER_ID )->count() )
				{
					//
					// Init local storage.
					//
					$child_fields = $mother_fields = [];

					//
					// Get location field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_LOCATION )) !== NULL )
						$child_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child location field not yet declared." );			// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_MOTHER_ID,
								self::kDATASET_OFFSET_LOCATION )) !== NULL )
						$mother_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother location field not yet declared." );		// !@! ==>

					//
					// Get team field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_TEAM )) !== NULL )
						$child_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child team field not yet declared." );				// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_MOTHER_ID,
								self::kDATASET_OFFSET_TEAM )) !== NULL )
						$mother_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother team field not yet declared." );			// !@! ==>

					//
					// Get cluster field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_CLUSTER )) !== NULL )
						$child_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child cluster field not yet declared." );			// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_MOTHER_ID,
								self::kDATASET_OFFSET_CLUSTER )) !== NULL )
						$mother_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother cluster field not yet declared." );			// !@! ==>

					//
					// Get household field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_HOUSEHOLD )) !== NULL )
						$child_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child household field not yet declared." );		// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_MOTHER_ID,
								self::kDATASET_OFFSET_HOUSEHOLD )) !== NULL )
						$mother_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother household field not yet declared." );		// !@! ==>

					//
					// Get mother field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_MOTHER )) !== NULL )
						$child_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child mother field not yet declared." );			// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_MOTHER_ID,
								self::kDATASET_OFFSET_IDENTIFIER )) !== NULL )
						$mother_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother identifier field not yet declared." );		// !@! ==>

				} // Has mother collection.

				//
				// Missing mother dataset collection.
				//
				else
					throw new RuntimeException(
						"Mother dataset collection not yet loaded." );			// !@! ==>

			} // Has child collection.

			//
			// Missing child dataset collection.
			//
			else
				throw new RuntimeException(
					"Child dataset collection not yet loaded." );				// !@! ==>

		} // Has fields.

		//
		// Missing fields.
		//
		else
			throw new RuntimeException(
				"Dataset fields not yet loaded." );								// !@! ==>

		//
		// Reset data dictionary.
		//
		$this->dictionaryList( $theDataset, self::kDDICT_INVALID_MOTHERS, [] );

		//
		// Reset error status.
		//
		$this->datasetStatus(
			$theDataset,
			FALSE,
			self::kSTATUS_INVALID_REFERENCES
		);

		//
		// Reset operation status.
		//
		$this->datasetStatus(
			$theDataset,
			FALSE,
			self::kSTATUS_CHECKED_REFS
		);

		//
		// Init pipeline.
		//
		$pipeline = [];

		//
		// Init selection group.
		//
		$selection = [];
		foreach( $child_fields as $field )
			$selection[ $field ] = '$' . $field;

		//
		// Add group.
		//
		$pipeline[] = [
			'$group' => [ '_id' => $selection ]
		];

		//
		// Aggregate.
		//
		$related =
			$this->datasetCollection( $theDataset )
				->aggregate( $pipeline, [ 'allowDiskUse' => TRUE ]
				);

		//
		// Iterate mother references.
		//
		$rels = [];
		foreach( $related as $relation )
		{
			//
			// Normalise relation.
			//
			$relation = $relation[ '_id' ]->getArrayCopy();
			if( count( $relation ) )
			{
				//
				// Set query.
				//
				$query = [];
				for( $i = 0; $i < count( $child_fields ); $i++ )
					$query[ $mother_fields[ $i ] ]
						= $relation[ $child_fields[ $i ] ];

				//
				// Check mother record.
				//
				$mother = $this->datasetCollection( self::kDDICT_MOTHER_ID )
					->findOne( $query );
				if( $mother !== NULL )
				{
					//
					// Set update commands.
					//
					$criteria = [
						'$set' => [ self::kCOLLECTION_OFFSET_MOTHER_ID => $mother[ '_id' ] ]
					];

					//
					// Update documents.
					//
					$this->datasetCollection( $theDataset )
						->updateMany( $relation, $criteria );

				} // Found mother.

				//
				// Handle missing mother.
				//
				else
				{
					//
					// Add invalid reference.
					//
					$index = count( $rels );
					$rels[ $index ] = [];
					$rels[ $index ][ self::kGROUP_CLUSTER ] = $relation;
					$rels[ $index ][ self::kGROUP_ROWS ] = [];

					//
					// Select offending rows.
					//
					$documents = $this->datasetCollection( $theDataset )->find( $relation );
					foreach( $documents as $document )
						$rels[ $index ][ self::kGROUP_ROWS ][]
							= $document[ '_id' ];

				} // Missing mother.

			} // Got cluster group.

		} // Iterating mother references.

		//
		// Set status.
		//
		$this->datasetStatus(
			$theDataset,
			TRUE,
			self::kSTATUS_CHECKED_REFS
		);

		//
		// Handle invalid references.
		//
		if( count( $rels ) )
		{
			//
			// Set status.
			//
			$this->datasetStatus(
				$theDataset,
				TRUE,
				self::kSTATUS_INVALID_REFERENCES
			);

			//
			// Load invalid references in data dictionary.
			//
			$this->dictionaryList( $theDataset, self::kDDICT_INVALID_MOTHERS, $rels );

		} // Found invalid references.

		//
		// Reset status.
		//
		else
			$this->datasetStatus(
				$theDataset,
				FALSE,
				self::kSTATUS_INVALID_REFERENCES
			);

		return $this->datasetStatus( $theDataset );									// ==>

	} // checkDatasetInvalidMothers.


	/*===================================================================================
	 *	checkDatasetInvalidHouseholds													*
	 *==================================================================================*/

	/**
	 * <h4>Check for invalid household references.</h4>
	 *
	 * This method is used by the public interface to check whether the dataset has
	 * invalid household references, the method expects a single parameter that represents
	 * the dataset identifier, by default it should be either {@link kDDICT_CHILD_ID} or
	 * {@link kDDICT_MOTHER_ID}.
	 *
	 * The method will raise an exception if the the household dataset was not yet loaded
	 * and if the location, team, cluster and household identifiers were not declared.
	 *
	 * The method will return the {@link kSTATUS_CHECKED_REFS} status code if no invalid
	 * references were found, or the {@link kSTATUS_INVALID_REFERENCES} status code if
	 * invalid references were found: in that case the method will fill the data dictionary
	 * {@link kDDICT_INVALID_MOTHERS} entry as follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kGROUP_CLUSTER}</tt>: This element contains an array holding the
	 * 		list of identifiers for the household reference: the location, team, cluster,
	 * 		and household numbers.
	 * 	<li><tt>{@link kGROUP_ROWS}</tt>: This element contains an array holding the list of
	 * 		rows featuring the invalid reference rows.
	 * </ul>
	 *
	 * The method will also load the {@link kCOLLECTION_OFFSET_HOUSEHOLD} household key into
	 * the child or mother collection.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return int					Status code.
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 *
	 * @uses dictionaryList()
	 * @uses datasetCollection()
	 * @uses datasetOffset()
	 * @uses dictionaryList()
	 * @uses datasetStatus()
	 */
	protected function checkDatasetInvalidHouseholds( string $theDataset )
	{
		//
		// Check dataset.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Only child and mother dataset selectors are expected: " .
					"provided [$theDataset]." );								// !@! ==>

		} // Checked dataset.

		//
		// Check dataset fields.
		//
		$columns = $this->dictionaryList( $theDataset, self::kDDICT_FIELDS );
		if( count( $columns  ) )
		{
			//
			// Check dataset collection.
			//
			if( $this->datasetCollection( $theDataset )->count() )
			{
				//
				// Check household collection.
				//
				if( $this->datasetCollection( self::kDDICT_HOUSEHOLD_ID )->count() )
				{
					//
					// Init local storage.
					//
					$dataset_fields = $household_fields = [];

					//
					// Get location field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_LOCATION )) !== NULL )
						$dataset_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child location field not yet declared." );			// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_HOUSEHOLD_ID,
								self::kDATASET_OFFSET_LOCATION )) !== NULL )
						$household_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother location field not yet declared." );		// !@! ==>

					//
					// Get team field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_TEAM )) !== NULL )
						$dataset_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child team field not yet declared." );				// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_HOUSEHOLD_ID,
								self::kDATASET_OFFSET_TEAM )) !== NULL )
						$household_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother team field not yet declared." );			// !@! ==>

					//
					// Get cluster field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_CLUSTER )) !== NULL )
						$dataset_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child cluster field not yet declared." );			// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_HOUSEHOLD_ID,
								self::kDATASET_OFFSET_CLUSTER )) !== NULL )
						$household_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother cluster field not yet declared." );			// !@! ==>

					//
					// Get household field.
					//
					if( ($tmp =
							$this->datasetOffset(
								$theDataset,
								self::kDATASET_OFFSET_HOUSEHOLD )) !== NULL )
						$dataset_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Child household field not yet declared." );		// !@! ==>
					if( ($tmp =
							$this->datasetOffset(
								self::kDDICT_HOUSEHOLD_ID,
								self::kDATASET_OFFSET_IDENTIFIER )) !== NULL )
						$household_fields[] = $tmp;
					else
						throw new RuntimeException(
							"Mother household field not yet declared." );		// !@! ==>

				} // Has household collection.

				//
				// Missing household dataset collection.
				//
				else
					throw new RuntimeException(
						"Household dataset collection not yet loaded." );		// !@! ==>

			} // Has dataset collection.

			//
			// Missing dataset collection.
			//
			else
				throw new RuntimeException(
					"Dataset collection not yet loaded." );						// !@! ==>

		} // Has fields.

		//
		// Missing fields.
		//
		else
			throw new RuntimeException(
				"Dataset fields not yet loaded." );								// !@! ==>

		//
		// Reset data dictionary.
		//
		$this->dictionaryList( $theDataset, self::kDDICT_INVALID_HOUSEHOLDS, [] );

		//
		// Reset error status.
		//
		$this->datasetStatus(
			$theDataset,
			FALSE,
			self::kSTATUS_INVALID_REFERENCES
		);

		//
		// Reset operation status.
		//
		$this->datasetStatus(
			$theDataset,
			FALSE,
			self::kSTATUS_CHECKED_REFS
		);

		//
		// Init pipeline.
		//
		$pipeline = [];

		//
		// Init selection group.
		//
		$selection = [];
		foreach( $dataset_fields as $field )
			$selection[ $field ] = '$' . $field;

		//
		// Add group.
		//
		$pipeline[] = [
			'$group' => [ '_id' => $selection ]
		];

		//
		// Aggregate.
		//
		$related =
			$this->datasetCollection( $theDataset )
				->aggregate( $pipeline, [ 'allowDiskUse' => TRUE ]
				);

		//
		// Iterate household references.
		//
		$rels = [];
		foreach( $related as $relation )
		{
			//
			// Normalise relation.
			//
			$relation = $relation[ '_id' ]->getArrayCopy();
			if( count( $relation ) )
			{
				//
				// Set query.
				//
				$query = [];
				for( $i = 0; $i < count( $dataset_fields ); $i++ )
					$query[ $household_fields[ $i ] ]
						= $relation[ $dataset_fields[ $i ] ];

				//
				// Check mother record.
				//
				$household =
					$this->datasetCollection(
						self::kDDICT_HOUSEHOLD_ID )->findOne( $query );
				if( $household !== NULL )
				{
					//
					// Set update commands.
					//
					$criteria = [
						'$set' => [
							self::kCOLLECTION_OFFSET_HOUSEHOLD_ID => $household[ '_id' ]
						]
					];

					//
					// Update documents.
					//
					$this->datasetCollection( $theDataset )
						->updateMany( $relation, $criteria );

				} // Found mother.

				//
				// Handle missing household.
				//
				else
				{
					//
					// Add invalid reference.
					//
					$index = count( $rels );
					$rels[ $index ] = [];
					$rels[ $index ][ self::kGROUP_CLUSTER ] = $relation;
					$rels[ $index ][ self::kGROUP_ROWS ] = [];

					//
					// Select offending rows.
					//
					$documents = $this->datasetCollection( $theDataset )->find( $relation );
					foreach( $documents as $document )
						$rels[ $index ][ self::kGROUP_ROWS ][]
							= $document[ '_id' ];

				} // Missing household.

			} // Got cluster group.

		} // Iterating household references.

		//
		// Set status.
		//
		$this->datasetStatus(
			$theDataset,
			TRUE,
			self::kSTATUS_CHECKED_REFS
		);

		//
		// Handle invalid references.
		//
		if( count( $rels ) )
		{
			//
			// Set status.
			//
			$this->datasetStatus(
				$theDataset,
				TRUE,
				self::kSTATUS_INVALID_REFERENCES
			);

			//
			// Load invalid references in data dictionary.
			//
			$this->dictionaryList( $theDataset, self::kDDICT_INVALID_HOUSEHOLDS, $rels );

		} // Found invalid references.

		//
		// Reset status.
		//
		else
			$this->datasetStatus(
				$theDataset,
				FALSE,
				self::kSTATUS_INVALID_REFERENCES
			);

		return $this->datasetStatus( $theDataset );									// ==>

	} // checkDatasetInvalidHouseholds.



/*=======================================================================================
 *																						*
 *							PROTECTED DATABASE UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	manageCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Manage a collection.</h4>
	 *
	 * This method can be used by public accessor methods to set or retrieve collections,
	 * including the necessary checks.
	 *
	 * @param Collection		   &$theMember			Reference to collection data member.
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 * @throws InvalidArgumentException
	 *
	 * @uses Database()
	 */
	protected function manageCollection( &$theMember, $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $theMember;														// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Collection cannot be deleted." );								// !@! ==>

		//
		// Check database.
		//
		$database = $this->Database();
		if( ! ($database instanceof Database) )
			throw new InvalidArgumentException(
				"Cannot create collection: " .
				"database not defined." );										// !@! ==>

		return
			$theMember
				= $this->Database()->selectCollection( (string)$theValue );			// ==>

	} // manageCollection.


	/*===================================================================================
	 *	originalCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Get the original collection.</h4>
	 *
	 * This method will return the original dataset collection connection, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset selector is invalid.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return Collection			Original collection.
	 * @throws InvalidArgumentException
	 *
	 * @uses Database()
	 */
	protected function originalCollection( string $theDataset )
	{
		//
		// Check dataset selector and set collection.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
				return
					$this->Database()
						->selectCollection(
							self::kNAME_PREFIX_ORIGINAL . self::kNAME_CHILD
						);															// ==>

			case self::kDDICT_MOTHER_ID:
				return
					$this->Database()
						->selectCollection(
							self::kNAME_PREFIX_ORIGINAL . self::kNAME_MOTHER
						);															// ==>

			case self::kDDICT_HOUSEHOLD_ID:
				return
					$this->Database()
						->selectCollection(
							self::kNAME_PREFIX_ORIGINAL . self::kNAME_HOUSEHOLD
						);															// ==>
		}

		throw new InvalidArgumentException(
			"Invalid dataset selector [$theDataset]." );						// !@! ==>

	} // originalCollection.



/*=======================================================================================
 *																						*
 *								PROTECTED EXPORT UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	exportDataset																	*
	 *==================================================================================*/

	/**
	 * <h4>Export a collection.</h4>
	 *
	 * This method can be used by the public interface to fill the provided Excel worksheet
	 * with data from a dataset collection.
	 *
	 * The method expects the Excel worksheet and the dataset selector as
	 * {@link kDDICT_CHILD_ID} for the child, {@link kDDICT_MOTHER_ID} for the mother, or
	 * {@link kDDICT_HOUSEHOLD_ID} for the household dataset; if the selector is not among
	 * these values, the method will raise an exception.
	 *
	 * If the collection is missing or empty, the method will do nothing.
	 *
	 * The method will return the number of written rows.
	 *
	 * @param PHPExcel_Worksheet	$theWorksheet		Export worksheet.
	 * @param string				$theDataset			Dataset selector.
	 * @return int					Number of written rows.
	 * @throws InvalidArgumentException
	 *
	 * @uses datasetCollection()
	 * @uses dictionaryList()
	 */
	protected function exportDataset( PHPExcel_Worksheet $theWorksheet, string $theDataset )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Check dataset.
		//
		if( $this->datasetCollection( $theDataset )->count() )
		{
			//
			// Init local storage.
			//
			$row = 1;

			//
			// Set ID field header.
			//
			$theWorksheet->setCellValueByColumnAndRow( 0, $row, '_id' );

			//
			// Add header.
			//
			$header = $this->dictionaryList( $theDataset, self::kDDICT_FIELDS );
			$fields = array_keys( $header );
			$columns = array_keys( $fields );
			foreach( $columns as $column )
				$theWorksheet->setCellValueByColumnAndRow(
					$column + 1, $row, $fields[ $column ]
				);

			//
			// Iterate data.
			//
			$cursor = $this->datasetCollection( $theDataset )->find();
			foreach( $cursor as $document )
			{
				//
				// Init local storage.
				//
				$row++;
				$document = $document->getArrayCopy();

				//
				// Set unit identifier.
				//
				$theWorksheet->setCellValueByColumnAndRow( 0, $row, $document[ '_id' ] );

				//
				// Iterate fields.
				//
				foreach( $columns as $column )
				{
					//
					// Get value.
					//
					if( array_key_exists( $fields[ $column ], $document ) )
					{
						//
						// Get value.
						//
						$value = $document[ $fields[ $column ] ];

						//
						// Get cell.
						//
						$cell = $theWorksheet->getCellByColumnAndRow( $column + 1, $row );

						//
						// Parse by type.
						//
						switch( $header[ $fields[ $column ] ][ self::kFIELD_TYPE ] )
						{
							case self::kTYPE_STRING:
								$cell->setValueExplicit(
									$value, PHPExcel_Cell_DataType::TYPE_STRING
								);
								break;

							case self::kTYPE_INTEGER:
							case self::kTYPE_NUMBER:
							case self::kTYPE_DOUBLE:
								$cell->setValueExplicit(
									$value, PHPExcel_Cell_DataType::TYPE_NUMERIC
								);
								break;

							case self::kTYPE_DATE:
								$date = new DateTime( $value );
								$stamp = $date->getTimestamp();
								$date_value = PHPExcel_Shared_Date::PHPToExcel( $stamp );
								$cell->setValue( $date_value );
								$cell
									->getStyle()
									->getNumberFormat()
									->setFormatCode(
										PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2
									);
								break;

							default:
								$cell->setValue( $value );
								break;

						} // Parsing data type.

					} // Column has value.

				} // Iterating columns.

			} // Iterating dataset.

			//
			// Format worksheet.
			//
			foreach( $theWorksheet->getColumnIterator() as $column )
				$theWorksheet
					->getColumnDimension( $column->getColumnIndex() )
					->setAutoSize( TRUE );

		} // Dataset not empty.

		return $row = 1;															// ==>

	} // exportDataset.


	/*===================================================================================
	 *	exportMerged																	*
	 *==================================================================================*/

	/**
	 * <h4>Export merged dataset.</h4>
	 *
	 * This method can be used by the public interface to fill the provided Excel worksheet
	 * with data from the merged datasets collection.
	 *
	 * The method expects the Excel worksheet, if the collection is missing or empty, the
	 * method will do nothing.
	 *
	 * The method will return the number of written rows.
	 *
	 * @param PHPExcel_Worksheet	$theWorksheet		Export worksheet.
	 * @return int					Number of written rows.
	 *
	 * @uses getChildFields()
	 * @uses getMotherFields()
	 * @uses getHouseholdFields()
	 * @uses ChildDatasetFields()
	 * @uses MotherDatasetFields()
	 * @uses HouseholdDatasetFields()
	 */
	protected function exportMerged( PHPExcel_Worksheet $theWorksheet )
	{
		//
		// Handle merged dataset.
		//
		if( $this->mSurvey->count() )
		{
			//
			// Init local storage.
			//
			$row = 1;

			//
			// Select default fields.
			//
			$child_fields = $this->getChildFields();
			$mother_fields = $this->getMotherFields();
			$household_fields = $this->getHouseholdFields();

			//
			// Set fields.
			//
			$header = [ '_id' => [ self::kFIELD_TYPE => self::kTYPE_INTEGER ] ];

			//
			// Set default group identifiers.
			//
			$header[ self::kCOLLECTION_OFFSET_DATE ]
				= [ self::kFIELD_TYPE => self::kTYPE_DATE ];
			$header[ self::kCOLLECTION_OFFSET_LOCATION ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];
			$header[ self::kCOLLECTION_OFFSET_TEAM ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];
			$header[ self::kCOLLECTION_OFFSET_CLUSTER ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];
			$header[ self::kCOLLECTION_OFFSET_HOUSEHOLD ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];
			$header[ self::kCOLLECTION_OFFSET_MOTHER ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];
			$header[ self::kCOLLECTION_OFFSET_IDENTIFIER ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];

			//
			// Load child fields.
			//
			foreach( $child_fields as $field )
				$header[ $field ] = $this->ChildDatasetFields()[ $field ];

			//
			// Set mother ID.
			//
			$header[ self::kCOLLECTION_OFFSET_MOTHER_ID ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];

			//
			// Load mother fields.
			//
			foreach( $mother_fields as $field )
				$header[ $field ] = $this->MotherDatasetFields()[ $field ];

			//
			// Set household ID.
			//
			$header[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ]
				= [ self::kFIELD_TYPE => self::kTYPE_INTEGER ];

			//
			// Load household fields.
			//
			foreach( $household_fields as $field )
				$header[ $field ] = $this->HouseholdDatasetFields()[ $field ];

			//
			// Add header.
			//
			$fields = array_keys( $header );
			$columns = array_keys( $fields );
			foreach( $columns as $column )
				$theWorksheet->setCellValueByColumnAndRow(
					$column, $row, $fields[ $column ]
				);

			//
			// Iterate data.
			//
			$cursor = $this->mSurvey->find();
			foreach( $cursor as $document )
			{
				//
				// Init local storage.
				//
				$row++;
				$document = $document->getArrayCopy();

				//
				// Iterate fields.
				//
				foreach( $columns as $column )
				{
					//
					// Get value.
					//
					if( array_key_exists( $fields[ $column ], $document ) )
					{
						//
						// Get value.
						//
						$value = $document[ $fields[ $column ] ];

						//
						// Parse by type.
						//
						switch( $header[ $fields[ $column ] ][ self::kFIELD_TYPE ] )
						{
							case self::kTYPE_DATE:
								$date = new DateTime( $value );
								$stamp = $date->getTimestamp();
								$date_value = PHPExcel_Shared_Date::PHPToExcel( $stamp );
								$theWorksheet->setCellValueByColumnAndRow(
									$column, $row, $date_value );
								$theWorksheet->getCellByColumnAndRow( $column, $row )
									->getStyle()
									->getNumberFormat()
									->SetFormatCode(
										PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2
									);
								break;

							default:
								$theWorksheet->setCellValueByColumnAndRow(
									$column, $row, $value );
								break;

						} // Parsing data type.

					} // Column has value.

				} // Iterating columns.

			} // Iterating dataset.

			//
			// Format worksheet.
			//
			foreach( $theWorksheet->getColumnIterator() as $column )
				$theWorksheet
					->getColumnDimension( $column->getColumnIndex() )
					->setAutoSize( TRUE );

		} // Has merged dataset.

		return $row = 1;															// ==>

	} // exportMerged.



/*=======================================================================================
 *																						*
 *							PROTECTED STATISTICAL UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	getMatrixTable																	*
	 *==================================================================================*/

	/**
	 * <h4>Get a matrix table.</h4>
	 *
	 * This method can be used to create a matrix of occurrences between two dataset fields.
	 * The method expects three parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset selector:
	 *	 <ul>
	 *	 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 *	 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 *	 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 *	 </ul>
	 * 	<li><b>$theRowTitle</b>: Title of field featured in rows.
	 * 	<li><b>$theRowField</b>: Field featured in rows.
	 * 	<li><b>$theColField</b>: Field featured in columns.
	 * </ul>
	 *
	 * The method will return a table having as rows the distinct values of
	 * <tt>$theRowField</tt> and as columns the cross reference with the distinct values
	 * of <tt>$theColField</tt>. The table has the following structure:
	 *
	 * <ul>
	 * 	<li><tt>Row 0</tt>: Header row:
	 * 	 <ul>
	 * 		<li><tt>Column 0</tt>: The value of <tt>$theRowTitle</tt>.
	 * 		<li><i>Other columns</i>: The distinct values of the <tt>$theColField</tt>.
	 * 	 </ul>
	 * 	<li><i>Other rows</i>: Data rows:
	 * 	 <ul>
	 * 		<li><tt>Column 0</tt>: The <tt>$theRowField</tt> value.
	 * 		<li><i>Other columns</i>: The occurrence count of the cross reference between
	 * 			the current <tt>$theRowField</tt> and the <tt>$theColField</tt>
	 * 			corresponding to the current column.
	 * 	 </ul>
	 * </ul>
	 *
	 * @param string				$theDataset			Dataset selector.
	 * @return array
	 *
	 * @uses datasetOffset()
	 * @uses datasetCollection()
	 * @uses getMatrix()
	 */
	protected function getMatrixTable( string $theDataset,
									   string $theRowTitle,
									   string $theRowField,
									   string $theColField )
	{
		//
		// Init local storage.
		//
		$table = [];

		//
		// Get X values.
		//
		$x =
			$this->datasetCollection( $theDataset )
				->distinct( $this->datasetOffset( $theDataset, $theRowField ) );
		sort( $x );

		//
		// Get Y values.
		//
		$y =
			$this->datasetCollection( $theDataset )
				->distinct( $this->datasetOffset( $theDataset, $theColField ) );
		sort( $y );

		//
		// Load header.
		//
		$table[ 0 ][ 0 ] = $theRowTitle;
		for( $i = 0; $i < count( $y ); $i++ )
			$table[ 0 ][ $i + 1 ] = $y[ $i ];

		//
		// Fill table.
		//
		$clusters_index = [];
		for( $i = 0; $i < count( $x ); $i++ )
		{
			//
			// Set X index.
			//
			$clusters_index[ $x[ $i ] ] = $i + 1;

			//
			// Set X number.
			//
			$table[ $i + 1 ][ 0 ] = $x[ $i ];

			//
			// Iterate Y.
			//
			for( $j = 0; $j < count( $y ); $j++ )
				$table[ $i + 1 ][ $j + 1 ] = '';

		} // Iterating X.

		//
		// Load table.
		//
		$cursor = $this->getMatrix( $theDataset, $theRowField, $theColField );
		foreach( $cursor as $document )
			$table[ $clusters_index[ $document[ 'X' ] ] ][ $document[ 'Y' ] ]
				= $document[ 'C' ];

		return $table;																// ==>

	} // getMatrixTable.


	/*===================================================================================
	 *	getMatrix																		*
	 *==================================================================================*/

	/**
	 * <h4>Get a matrix.</h4>
	 *
	 * This method can be used to retrieve a lisy of cross-reference occurrences between two
	 * dataset fields. The method expects three parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset selector:
	 *	 <ul>
	 *	 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 *	 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 *	 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 *	 </ul>
	 * 	<li><b>$theRowField</b>: Field featured in rows.
	 * 	<li><b>$theColField</b>: Field featured in columns.
	 * </ul>
	 *
	 * The method will return a cursor with documents having the following structure:
	 *
	 * <ul>
	 * 	<li><tt>X</tt>: Cluster number.
	 * 	<li><tt>Y</tt>: Team number.
	 * 	<li><tt>C</tt>: Combination occurrences.
	 * </ul>
	 *
	 * @param string				$theDataset			Dataset selector.
	 * @return \MongoDB\Driver\Cursor
	 * @throws InvalidArgumentException
	 *
	 * @uses datasetOffset()
	 * @uses datasetCollection()
	 */
	protected function getMatrix( string $theDataset,
								  string $theRowField,
								  string $theColField )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Get relationships.
		//
		$x = $this->datasetOffset( $theDataset, $theRowField );
		$y = $this->datasetOffset( $theDataset, $theColField );
		$pipeline = [
			[ '$group' => [
				'_id' => [ 'X' => '$' . $x, 'Y' => '$' . $y ],
				'count' => [ '$sum' => 1 ] ] ],
			[ '$project' => [
				'_id' => false,
				'X' => '$' . '_id.X',
				'Y' => '$' . '_id.Y',
				'C' => '$' . 'count'
			] ]
		];

		return $this->datasetCollection( $theDataset )->aggregate( $pipeline );		// ==>

	} // getMatrix.



/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	getChildFields																	*
	 *==================================================================================*/

	/**
	 * <h4>Get significant child fields.</h4>
	 *
	 * This method can be used to retrieve the list of child collection fields excluding
	 * default fields.
	 *
	 * The method assumes all required information is loaded into the object.
	 *
	 * @return array
	 *
	 * @uses ChildDatasetDateOffset()
	 * @uses ChildDatasetLocationOffset()
	 * @uses ChildDatasetTeamOffset()
	 * @uses ChildDatasetClusterOffset()
	 * @uses ChildDatasetHouseholdOffset()
	 * @uses ChildDatasetMotherOffset()
	 * @uses ChildDatasetIdentifierOffset()
	 * @uses ChildDatasetFields()
	 */
	protected function getChildFields()
	{
		//
		// Init default fields.
		//
		$defaults = [
			$this->ChildDatasetDateOffset(),
			$this->ChildDatasetLocationOffset(),
			$this->ChildDatasetTeamOffset(),
			$this->ChildDatasetClusterOffset(),
			$this->ChildDatasetHouseholdOffset(),
			$this->ChildDatasetMotherOffset(),
			$this->ChildDatasetIdentifierOffset()
		];

		return
			array_diff(
				array_keys( $this->ChildDatasetFields() ),
				$defaults,
				[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID,
					self::kCOLLECTION_OFFSET_MOTHER_ID ]
			);																		// ==>

	} // getChildFields.


	/*===================================================================================
	 *	getMotherFields																	*
	 *==================================================================================*/

	/**
	 * <h4>Get significant mother fields.</h4>
	 *
	 * This method can be used to retrieve the list of mother collection fields excluding
	 * default fields.
	 *
	 * The method assumes all required information is loaded into the object.
	 *
	 * @return array
	 *
	 * @uses MotherDatasetDateOffset()
	 * @uses MotherDatasetLocationOffset()
	 * @uses MotherDatasetTeamOffset()
	 * @uses MotherDatasetClusterOffset()
	 * @uses MotherDatasetHouseholdOffset()
	 * @uses MotherDatasetIdentifierOffset()
	 * @uses MotherDatasetFields()
	 */
	protected function getMotherFields()
	{
		//
		// Init default fields.
		//
		$defaults = [
			$this->MotherDatasetDateOffset(),
			$this->MotherDatasetLocationOffset(),
			$this->MotherDatasetTeamOffset(),
			$this->MotherDatasetClusterOffset(),
			$this->MotherDatasetHouseholdOffset(),
			$this->MotherDatasetIdentifierOffset()
		];

		return
			array_diff(
				array_keys( $this->MotherDatasetFields() ),
				$defaults,
				[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ]
			);																		// ==>

	} // getMotherFields.


	/*===================================================================================
	 *	getHouseholdFields																*
	 *==================================================================================*/

	/**
	 * <h4>Get significant mother fields.</h4>
	 *
	 * This method can be used to retrieve the list of mother collection fields excluding
	 * default fields.
	 *
	 * The method assumes all required information is loaded into the object.
	 *
	 * @return array
	 *
	 * @uses HouseholdDatasetDateOffset()
	 * @uses HouseholdDatasetLocationOffset()
	 * @uses HouseholdDatasetTeamOffset()
	 * @uses HouseholdDatasetClusterOffset()
	 * @uses HouseholdDatasetIdentifierOffset()
	 * @uses HouseholdDatasetFields()
	 */
	protected function getHouseholdFields()
	{
		//
		// Init default fields.
		//
		$defaults = [
			$this->HouseholdDatasetDateOffset(),
			$this->HouseholdDatasetLocationOffset(),
			$this->HouseholdDatasetTeamOffset(),
			$this->HouseholdDatasetClusterOffset(),
			$this->HouseholdDatasetIdentifierOffset()
		];

		return
			array_diff(
				array_keys( $this->HouseholdDatasetFields() ),
				$defaults
			);																		// ==>

	} // getHouseholdFields.




} // class SMARTLoader.


?>
