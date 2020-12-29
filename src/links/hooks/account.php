<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(canMakePlgLinks()){
include $abs_us_root.$us_url_root."usersc/plugins/links/assets/link_management.php";
}
