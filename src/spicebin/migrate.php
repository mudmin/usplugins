<?php
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.
include "plugin_info.php";
if (in_array($user->data()->id, $master_account) && pluginActive($plugin_name,true)){
//all actions should be performed here.

//check which updates have been installed
$count = 0;
$db = DB::getInstance();

//Make sure the plugin is installed and get the existing updates
$checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC > 0){
  $check = $checkQ->first();
  if($check->updates == ''){
  $existing = []; //deal with not finding any updates
  }else{
  $existing = json_decode($check->updates);
  }

  //list your updates here from oldest at the top to newest at the bottom.
  //Give your update a unique update number/code.

  //here is an example
  $update = '00001';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00002';
  if(!in_array($update,$existing)){

  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $checkC = $db->query("SELECT * FROM plg_spicebin_lang")->count();

  if($checkC < 1){
    $db->query("
      INSERT INTO `plg_spicebin_lang` (`id`, `lang`, `common`) VALUES
      (1, 'apl', 0),
      (2, 'asciiarmor', 0),
      (3, 'asn.1', 0),
      (4, 'asterisk', 0),
      (5, 'clike', 1),
      (6, 'clojure', 0),
      (7, 'cmake', 0),
      (8, 'cobol', 0),
      (9, 'coffeescript', 0),
      (10, 'commonlisp', 0),
      (11, 'crystal', 0),
      (12, 'css', 0),
      (13, 'cypher', 0),
      (14, 'd', 0),
      (15, 'dart', 0),
      (16, 'diff', 0),
      (17, 'django', 0),
      (18, 'dockerfile', 0),
      (19, 'dtd', 0),
      (20, 'dylan', 0),
      (21, 'ebnf', 0),
      (22, 'ecl', 0),
      (23, 'eiffel', 0),
      (24, 'elm', 0),
      (25, 'erlang', 0),
      (26, 'factor', 0),
      (27, 'fcl', 0),
      (28, 'forth', 0),
      (29, 'fortran', 0),
      (30, 'gas', 0),
      (31, 'gfm', 0),
      (32, 'gherkin', 0),
      (33, 'go', 0),
      (34, 'groovy', 0),
      (35, 'haml', 0),
      (36, 'handlebars', 0),
      (37, 'haskell', 0),
      (38, 'haskell-literate', 0),
      (39, 'haxe', 0),
      (40, 'htmlembedded', 0),
      (41, 'htmlmixed', 1),
      (42, 'http', 0),
      (43, 'idl', 0),
      (44, 'javascript', 1),
      (45, 'jinja2', 0),
      (46, 'jsx', 0),
      (47, 'julia', 0),
      (48, 'livescript', 0),
      (49, 'lua', 0),
      (50, 'markdown', 0),
      (51, 'mathematica', 0),
      (52, 'mbox', 0),
      (53, 'mirc', 0),
      (54, 'mllike', 0),
      (55, 'modelica', 0),
      (56, 'mscgen', 0),
      (57, 'mumps', 0),
      (58, 'nginx', 0),
      (59, 'nsis', 0),
      (60, 'ntriples', 0),
      (61, 'octave', 0),
      (62, 'oz', 0),
      (63, 'pascal', 0),
      (64, 'pegjs', 0),
      (65, 'perl', 0),
      (66, 'php', 1),
      (67, 'pig', 0),
      (68, 'powershell', 0),
      (69, 'properties', 0),
      (70, 'protobuf', 0),
      (71, 'pug', 0),
      (72, 'puppet', 0),
      (73, 'python', 1),
      (74, 'q', 0),
      (75, 'r', 0),
      (76, 'rpm', 0),
      (77, 'rst', 0),
      (78, 'ruby', 1),
      (79, 'rust', 0),
      (80, 'sas', 0),
      (81, 'sass', 0),
      (82, 'scheme', 0),
      (83, 'shell', 0),
      (84, 'sieve', 0),
      (85, 'slim', 0),
      (86, 'smalltalk', 0),
      (87, 'smarty', 0),
      (88, 'solr', 0),
      (89, 'soy', 0),
      (90, 'sparql', 0),
      (91, 'spreadsheet', 0),
      (92, 'sql', 0),
      (93, 'stex', 0),
      (94, 'stylus', 0),
      (95, 'swift', 0),
      (96, 'tcl', 0),
      (97, 'textile', 0),
      (98, 'tiddlywiki', 0),
      (99, 'tiki', 0),
      (100, 'toml', 0),
      (101, 'tornado', 0),
      (102, 'troff', 0),
      (103, 'ttcn', 0),
      (104, 'ttcn-cfg', 0),
      (105, 'turtle', 0),
      (106, 'twig', 0),
      (107, 'vb', 0),
      (108, 'vbscript', 0),
      (109, 'velocity', 0),
      (110, 'verilog', 0),
      (111, 'vhdl', 0),
      (112, 'vue', 0),
      (113, 'wast', 0),
      (114, 'webidl', 0),
      (115, 'xml', 0),
      (116, 'xquery', 0),
      (117, 'yacas', 0),
      (118, 'yaml', 0),
      (119, 'yaml-frontmatter', 0),
      (120, 'z80', 0)
    ");
    // logger(0,"Diag",$db->errorString());

  }

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }


  //after all updates are done. Keep this at the bottom.
  $new = json_encode($existing);
  $db->update('us_plugins',$check->id,['updates'=>$new,'last_check'=>date("Y-m-d H:i:s")]);
  if(!$db->error()) {
    logger($user->data()->id,"Migrations","$count migration(s) successfully triggered for $plugin_name");
  } else {
   	logger($user->data()->id,"USPlugins","Failed to save updates, Error: ".$db->errorString());
  }
}//do not perform actions outside of this statement
}
