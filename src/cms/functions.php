<?php
if(!function_exists('displayLayout')){
  function displayLayout($content){
    global $abs_us_root, $us_url_root,$db;
    $check = cmsPerms($content->id);
    if($check['status'] == true){
    $layoutQ = $db->query("SELECT * FROM plg_cms_layouts WHERE id = ?",[$content->layout]);
    $layoutC = $layoutQ->count();
    if($layoutC < 1){
      $layoutQ = $db->query("SELECT * FROM plg_cms_layouts LIMIT 1");
      $layoutC = $layoutQ->count();
      if($layoutC < 1){
        echo "<h3 align='center'>No layouts found</h3>";
        die;
      }else{
        $layout = $layoutQ->first();
      }
    }else{
      $layout = $layoutQ->first();
    }
    $blob = $layout->layout;
    $strings = explode("<!>",$blob);
    $types = ['txt','wid','con','aut','pub','mod','cat','sts','nam'];
    foreach($strings as $s){
      $type = substr($s,0,3);
      if(in_array($type,$types)){
        $codes = substr($s,3);

      }else{
        $type = "txt"; //set a default
        $codes = $s;
      }
      if($type == 'con'){
        echo htmlspecialchars_decode($content->content);
      }
      if($type == 'nam'){
        echo $content->title;
      }
      if($type == 'aut'){
        echouser($content->author);
      }
      if($type == 'pub'){
        echo $content->date_published;
      }
      if($type == 'mod'){
        echo $content->last_modified;
      }
      if($type == 'cat'){
        echo CMSCat($content->category);
      }
      if($type == 'sts'){
        if($content->status == 0){
          echo "Draft";
        }
        if($content->status == 1){
          echo "Published";
        }
      }

      $code = explode("<@>",$codes);
      if($type == 'wid'){
        $widgetQ = $db->query("SELECT * FROM plg_cms_widgets WHERE id = ?",[$code[0]]);
        $widgetC = $widgetQ->count();
        if($widgetC > 0){
          $widget = $widgetQ->first();
          if($widget->widget_type == 1){
            $widget->file = str_replace(" ","",$widget->file);
            if(file_exists($abs_us_root.$us_url_root.'usersc/plugins/cms/widgets/'.$widget->file.".php")){
                  include $abs_us_root.$us_url_root.'usersc/plugins/cms/widgets/'.$widget->file.".php";
                }elseif($abs_us_root.$us_url_root.'usersc/plugins/cms/widgets/'.$widget->file){
                    include $abs_us_root.$us_url_root.'usersc/plugins/cms/widgets/'.$widget->file;
                }else{
                  echo "<h3 align='center'>Widget not found</h3>";
                 echo " usersc/plugins/cms/widgets/".$widget->file."php";
                }
          }
          if($widget->widget_type == 2){
            echo htmlspecialchars_decode($widget->content);
          }
        }
      }

      if($type == "txt"){
        foreach($code as $c){
          $c = trim($c);
          echo htmlspecialchars_decode( $c );

        }
      }
    }
  }else{
    echo "<h3 align='center'>".$check['msg']."</h3>";
  }
  }
}

if(!function_exists('cmsCatTree')){
  function cmsCatTree($parent_id = 0, $sub_mark = '', $selected = 0){
      global $db;
      $sub_mark .="&nbsp;&nbsp;";
      $q = $db->query("SELECT * FROM plg_cms_categories WHERE subcat_of = $parent_id ORDER BY category ASC");
      $c = $q->count();
      if($c > 0){
        $results = $q->results();
          foreach($results as $r){?>
              <option value="<?=$r->id?>" <?php if($r->id == $selected){echo "selected";}?>><?=$sub_mark.$r->category?></option>
              <?php cmsCatTree($r->id,$sub_mark,$selected);
          }
      }
  }
}

if(!function_exists('echoCMSCat')){
  function echoCMSCat($cat,$sep = " > ", $string = ""){
    global $db;
    $q = $db->query("SELECT category FROM plg_cms_categories WHERE id = ?",[$cat]);
    $c = $q->count();
    if($c > 0){
      $f = $q->first();
      echo $f->category;
    }else{
      echo "--";
    }
  }
}

if(!function_exists('CMSCat')){
  function CMSCat($cat,$sep = " > ", $string = ""){
    global $db;
    $q = $db->query("SELECT category,subcat_of FROM plg_cms_categories WHERE id = ?",[$cat]);
    $c = $q->count();
    if($c > 0){
      $f = $q->first();
      if($string == ""){
        $string = $f->category;
      }else{
        $string = $f->category . $sep . $string;
      }
      if($f->subcat_of > 0){
        $string = CMSCat($f->subcat_of,$sep,$string);
      }
    }
    return $string;
  }
}

if(!function_exists('cmsPerms')){
  function cmsPerms($article_id){
    global $user,$db;
    $msg = [];
    $articleQ = $db->query("SELECT id,category,status FROM plg_cms_content WHERE id = ?",[$article_id]);
    $articleC = $articleQ->count();
    if($articleC < 1){
      $msg = ['status'=>false,"msg"=>"Article not found"];
      return $msg;
    }else{
      $article = $articleQ->first();
    }

    if($user->isLoggedIn() && hasPerm([2],$user->data()->id)){
      $msg = ['status'=>true,"msg"=>"Admin access"];
      return $msg;
    }

    if($article->status == 0){
      $msg = ['status'=>false,"msg"=>"Article is private"];
      return $msg;
    }

    $checkQ = $db->query("SELECT * FROM plg_cms_categories WHERE id = ?",[$article->category]);
    $checkC = $checkQ->count();
    if($checkC < 1){
      $msg = ['status'=>false,"msg"=>"Category error"];
      return $msg;
    }else{
      $check = $checkQ->first();
      $perms = explode(",",$check->perms);
      if(in_array(0,$perms)){
        $msg = ['status'=>true,"msg"=>"Public access"];
        return $msg;
      }
      if($user->isLoggedIn() && hasPerm($perms,$user->data()->id)){
        $msg = ['status'=>true,"msg"=>"Category access"];
        return $msg;
      }else{
        $msg = ['status'=>false,"msg"=>"Permission level failed"];
        return $msg;
      }
    }
    $msg = ['status'=>false,"msg"=>"Unspecified error"];
    return $msg;
  }
}

if(!function_exists("echoCatPerms")){
function echoCatPerms($cat){
  global $db;
  $string = "";
  $permsQ = $db->query("SELECT perms from plg_cms_categories WHERE id = ?",[$cat]);
  $permsC = $permsQ->count();
  if($permsC > 0){
    $perms = $permsQ->first();
    $perms = explode(",",$perms->perms);
    foreach($perms as $p){
      if($p == 0){
        $string .= "Wide Open, ";
      }else{

        $q = $db->query("SELECT name from permissions WHERE id = ?",[$p]);
        $c = $q->count();
        if($c > 0){
          $f = $q->first();
          $string .= $f->name.", ";
        }
      }
    }
    $string = substr($string,0,-2);
    echo $string;
  }

}
}
