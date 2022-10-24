<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted Leave this line in place
global $db,$spreadsheet;
//set who is allowed to create this report
$access = false;
if(isset($user) && $user->isLoggedIn() && hasPerm([2],$user->data()->id)){
  $access = true;
}

if($access){ //wrap your whole report in the if($access)
  $title = "Users Report";
  $format = 'Xlsx';
  
  // Create new Spreadsheet object
  // You can leave all this stuff default, but you're free to change it

  $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
  $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

  // Set document metadata ... or don't
  $spreadsheet->getProperties()->setCreator($title)
      ->setLastModifiedBy($title)
      ->setTitle($title)
      ->setSubject($title)
      ->setDescription($title)
      ->setKeywords($title)
      ->setCategory($title);

//create a reports array to hold your worksheet(s)
$reports = [];

//every worksheet is an item in the array.
//title is the tab name for the worksheet
//data is an array containing your data
//headings are your column headings.  Ideally there are the same number of headings as data columns
$reports[0]['title'] = "First Names";
$reports[0]['data'] = $db->query("SELECT id, fname FROM users")->results(true);
$reports[0]['headings'] = ['ID',"First Name"];

//to add a second worksheet, add another set of data
$reports[1]['title'] = "Last Names";
$reports[1]['data'] = $db->query("SELECT id, lname FROM users")->results(true);

//this time I'll generate the headings from the columns in the database to show you how to do it dynamically
foreach($reports[1]['data'][0] as $k=>$v){
  $reports[1]['headings'][] = ucfirst($k);
}

} //make sure everything is enclosed in this if($access) statement
