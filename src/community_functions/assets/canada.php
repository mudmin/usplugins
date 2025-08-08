<?php
if(!function_exists('provFromAbbrevCA')){
  function provFromAbbrevCA($string,$french="false"){
    $string = strtoupper($string);
    $length = strlen($string);
    if($length == 2){
      $codes = providencesCA($french);
    }else{
      return false;
    }
    if(array_key_exists($string,$codes)){
      return $codes[$string];
    }else{
      return false;
    }
}
}

if(!function_exists('provincesCA')){
  function providencesCA($french=""){
    $french = strtolower($french);
  if($french == "french"){
    $prov =   array(
      'AB' => "Alberta",
      'BC' => "Colombie Britannique",
      'MB' => "Manitoba",
      'NB' => "Nouveau-Brunswick",
      'NL' => "Terre-Neuve-et-Labrador",
      'NT' => "Territoires du Nord-Ouest",
      'NS' => "Nouvelle-Écosse",
      'NU' => "Nunavut",
      'ON' => "Ontario",
      'PE' => "Île du Prince-Édouard",
      'QC' => "Quebec",
      'SK' => "Saskatchewan",
      'YT' => "Territoire du Yukon",
    );
}else{
  $prov =   array(
  'AB' => "Alberta",
  'BC' => "British Columbia",
  'MB' => "Manitoba",
  'NB' => "New Brunswick",
  'NL' => "Newfoundland",
  'NT' => "Northwest Territories",
  'NS' => "Nova Scotia",
  'NU' => "Nunavut",
  'ON' => "Ontario",
  'PE' => "Prince Edward Island",
  'QC' => "Quebec",
  'SK' => "Saskatchewan",
  'YT' => "Yukon",
);

}
return $prov;
  }
}
