<?php
require_once "../../../users/init.php";
if(!pluginActive("reports",true)){
  die("Plugin not active");
}
$db = DB::getInstance();
if(!isset($user) || $user->isLoggedIn()){
  $uid = 0;
}else{
  $uid = $user->data()->id;
}
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;
$spreadsheet = new Spreadsheet();

$report = Input::get('report');
if(file_exists($abs_us_root.$us_url_root."usersc/plugins/reports/reportfiles/".$report.".php")){
  include $abs_us_root.$us_url_root."usersc/plugins/reports/reportfiles/".$report.".php";
  global $access, $spreadsheet;
  if(!$access){
    logger($uid,"reports","Tried to run $report without permission");
    die("You do not have access to generate this report");

  }
}else{
  logger($uid,"reports","Tried to run $report that doesn not exist");
  die("This report does not exist");
}

switch($format)
{
    case 'Cvs':
        $fileext = 'csv';
        $writerclass = 'Csv';
        break;
    case 'Pdf':
        $fileext = 'pdf';
        $writerclass = 'Dompdf';
        break;
    case 'Ods':
        $fileext = 'ods';
        $writerclass = 'Ods';
        break;
    default:
    case 'Xlsx':
        $fileext = 'xlsx';
        $writerclass = 'Xlsx';
        break;
}

function createExcelHeading($data){
  $headings = [];
  foreach($data[0] as $k=>$v){
    $headings[] = ucfirst($k);
  }
  return $headings;
}

$spreadsheet->removeSheetByIndex(0);
for ($i=0; $i < count($reports) ; $i++) {
  $rowID = 1;
  $thisSheet = $spreadsheet->createSheet();
  $thisSheet->setTitle($reports[$i]['title']);

  //grab headings
  if(isset($reports[$i]['headings'])){
    $headings = $reports[$i]['headings'];
  }else{
    $headings = createExcelHeading($reports[$i]['data']);
  }
  $columnID = 'A';
  foreach($headings as $k=>$value){
  $spreadsheet->setActiveSheetIndex($i)->setCellValue($columnID.$rowID, $value);
  $spreadsheet->getActiveSheet()->getStyle($columnID.$rowID)
    ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
  $columnID++;
  }
  $rowID++;


  //insert data
  $columns = [];
  foreach($reports[$i]['data'] as $k=>$v){
  $columnID = 'A';
    foreach($v as $key=>$value){
    $columns[] = $columnID;
    $spreadsheet->setActiveSheetIndex($i)->setCellValue($columnID.$rowID, $value);
    $columnID++;
  }
  $rowID++;
}

foreach($columns as $c){
  $spreadsheet->getActiveSheet()->getColumnDimension($c)->setAutoSize(true);
}

}


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$spreadsheet->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$title.'.'.$fileext.'"');
header('Cache-Control: max-age=0');
ob_clean();
$writer = IOFactory::createWriter($spreadsheet, $writerclass);
$writer->save('php://output');
exit;
