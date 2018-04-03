<?php
include_once( "./include/global.php" );
include_once( "./include/utility.php" );
include_once( "./include/fileuploader.php" );
include_once( "./include/phpexcel/PHPExcel.php" );

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

// the data array to return
$ret = array();
$ret["status"] = "success";

function validLangPack($lang) //jinho added for K01 lang check
{
	$mylang = ["en_US","zh_CN","zh_TW",
	"cs_CZ","de_DE","fr_FR","es_ES","es_US",
	"it_IT","ja_JP","ko_KR","nl_NL","pl_PL",
	"ru_RU","tr_TR","vi_VN"];
	foreach ($mylang as $item){
		if ($lang == $item)
			return true;
	}
	return false;
}
function GetLanguageArrayAndStartColumn( $worksheet, &$language_array, &$start_column_id )
{
	// reset output data
	$start_column_id = 0;
	$language_array = array();

	// start from 1
	$column_id = 1;

	// loop to find all languages
	$cell_value = $worksheet->getCellByColumnAndRow($column_id, 1)->getValue();
	while( $cell_value != "" )
	{	
		// lang column not start yet
		if( $start_column_id <= 0 )
		{
			if( $cell_value == "en_US" )
			{
				 $start_column_id = $column_id;
				 array_push( $language_array, $cell_value );
			}
		}
		// already start lang column
		else
			array_push( $language_array, $cell_value );
		
		// increment search column count
		$column_id++;
		
		// get the cell value
		$cell_value = $worksheet->getCellByColumnAndRow($column_id, 1)->getValue();
	} 
}

switch( $_GET["command"] )
{
	// switch language
	case "switch":
		// check if language uploaded
		if( !isset($_GET["user_language"]) )
		{
			SetErrorState($ret, "Please select the language you prefer.");
			break;
		}

		//jinho do check
		if (!validLangPack($_GET["user_language"])) {
			SetErrorState($ret, "Please select the language you prefer.");
			break;
		}  

		// setup th current language
		$_SESSION["user_language"] = $_GET["user_language"];
		$ret["status"] = "success";
		break;

	// update language file
	case "update":
		if( !isset($_SESSION["user_group_id"]) || $_SESSION["user_group_id"] != ADMIN_GROUP_ID )
		{
			SetErrorState($ret, "You don't have permission to update language table.");
			break;
		}

		// get the total path
		$path_to_handle = $_SERVER["DOCUMENT_ROOT"] . dirname($_SERVER["PHP_SELF"]) . LANGUAGE_UPLOAD_PATH;
	
		// set size limit to 10MB
		$sizeLimit = 10 * 1024 * 1024;
		
		// handle upload
		$uploader = new qqFileUploader(array(), $sizeLimit);
		$result = $uploader->handleUpload($path_to_handle, TRUE);
		
		// merge the result to the data to return
		$ret = array_merge($ret, $result);

		// rename file
		rename( $path_to_handle . $_GET['qqfile'], $path_to_handle . LANGUAGE_TABLE_NAME );
		break;

	// apply the language table
	case "apply":
		if( !isset($_SESSION["user_group_id"]) || $_SESSION["user_group_id"] != ADMIN_GROUP_ID )
		{
			SetErrorState($ret, "You don't have permission to update language table.");
			break;
		}

		// prepare the filename
		$filename_to_handle = $_SERVER["DOCUMENT_ROOT"] . dirname($_SERVER["PHP_SELF"]) . LANGUAGE_UPLOAD_PATH . LANGUAGE_TABLE_NAME;

		// check if file exist
		if( !file_exists($filename_to_handle) )
		{
			SetErrorState($ret, "Please upload language table first.");
			break;
		}

		// read excel
		$objPHPExcel = PHPExcel_IOFactory::load($filename_to_handle);

		// get sheet
		$worksheet = $objPHPExcel->getSheet();

		// get language array
		GetLanguageArrayAndStartColumn( $worksheet, $language_array, $start_column_id );

		// check if get any language
		if( count($language_array) <= 1 )
		{
			SetErrorState($ret, "Please upload language table with the correct format.");
			break;
		}
		
		// create po files
		for( $i=1, $count=count($language_array); $i<$count; $i++ )
		{
			// prepare po filename
			$po_filename = $_SERVER["DOCUMENT_ROOT"] . dirname($_SERVER["PHP_SELF"]) . LANGUAGE_UPLOAD_PATH . $language_array[$i] . ".po";
			
			// prepare mo filename
			$mo_filename = $_SERVER["DOCUMENT_ROOT"] . dirname($_SERVER["PHP_SELF"]) . "/locale/" . $language_array[$i] . "/LC_MESSAGES/messages.mo";
			
			// open po file to write
			$fd = fopen($po_filename, "w");
			
			// loop to write po file
			$row_id = 2; // start from row 2
			$msgid = $worksheet->getCellByColumnAndRow($start_column_id, $row_id)->getValue();
			$msgstr = $worksheet->getCellByColumnAndRow($start_column_id+$i, $row_id)->getValue();
			while( $msgid != "" )
			{
				// skip if not enter
				if( $msgstr != "" )
				{
					// write to file
					fwrite( $fd, "msgid \"" . $msgid . "\"\n" );
					fwrite( $fd, "msgstr \"" . $msgstr . "\"\n" );
				}
				
				// increment row count
				$row_id++;
				
				// get next
				$msgid = $worksheet->getCellByColumnAndRow($start_column_id, $row_id)->getValue();
				$msgstr = $worksheet->getCellByColumnAndRow($start_column_id+$i, $row_id)->getValue();
			}
			
			// close the po file
			fclose( $fd );
			
			// create mo
			system( "/usr/bin/msgfmt " . $po_filename . " -o ". $mo_filename );
		}
		break;

	default:
		break;
}

//echo htmlspecialchars(json_encode($ret), ENT_NOQUOTES);
echo json_encode( $ret );
?>
