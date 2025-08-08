  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
//several functions lifted from
// https://github.com/vanilla-php/benchmark-php/blob/master/benchmark.php

include "plugin_info.php";
pluginActive($plugin_name);
$token = Token::generate();
 ?>
<script src="<?=$us_url_root?>usersc/plugins/benchmark/assets/h2c.min.js"></script>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
        <?php
        $time = -microtime(true);
        $times = [];
        for ($i=0; $i < rand(1000,4000); ++$i) {
            $hash = randomstring(15);
        }
        $time += microtime(true);
        echo "Hash: $hash iterations:$i time: ",sprintf('%f', $time),PHP_EOL;


         ?>
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Benchmark Your Server</h1>
            <p>This fun little tool gives you the ability to benchmark your server against the worst performing
                    laptop Dan has owned in the past 10 years...the Acer Swift 1 (but hey, the battery life is awesome).</p>
            <p>If you want to diagnose slow loading pages, I recommend the "Performance Checker" plugin.</p>
          <form class="" action="" method="get">
            <input type="hidden" name="go" value="1">
            <input type="hidden" name="view" value="plugins_config">
            <input type="hidden" name="plugin" value="benchmark">
            <label for="">Give this run a name</label>
            <input type="text" name="benchname" value="">
            <input type="submit" name="submit" value="Go">
            <br><strong>Your screen will refresh for a few seconds</strong>
          </form>
<?php
$results = "";
$go = Input::get('go');
if($go == 1){
  $results = false;
  $start = -microtime(true);
  $data = [];
  $data['sysinfo']['php_version'] = PHP_VERSION;
  $data['sysinfo']['platform'] = PHP_OS;
  $data['sysinfo']['xdebug'] = in_array('xdebug', get_loaded_extensions());

  //generate random strings
  $run = -microtime(true);
  $x = 10000;
  for ($i=0; $i < $x ; $i++) {
    $blah = randomstring(100);
  }
  $run += microtime(true);
  //dump($x." random strings in");
  //dump(sprintf('%f', $run));
  $data['random'] = sprintf('%f', $run);

  //bcrypt a password
  $run = -microtime(true);
  $blah = password_hash("password", PASSWORD_BCRYPT, array('cost' => 14));
  $run += microtime(true);
  //dump("Bcrypt a Password");
  //dump(sprintf('%f', $run));
  $data['bcrypt'] = sprintf('%f', $run);

  //count to a million
  $run = -microtime(true);
  $x = 1000000;
  $y = 0;
  for ($i=1; $i <= $x ; $i++) {
    $y++;
  }
  $run += microtime(true);
  //dump("Count to a million");
  //dump(sprintf('%f', $run));
  $data['million'] = sprintf('%f', $run);

  //insert 1000 rows into the db
  $run = -microtime(true);
  $x = 1000;
  for ($i=0; $i < $x ; $i++) {
    $rando = randomstring(255);
    $db->insert('plg_benchmark',['total'=>9,'benchdata'=>'UserSpice Rulez','randostring'=>$rando]);
    $db->insert('plg_benchmark_join',['total'=>9,'benchdata'=>'UserSpice Rulez','randostring'=>$rando]);
  }
  $run += microtime(true);
  //dump("Insert 1000 DB Rows");
  //dump(sprintf('%f', $run));
  $data['insert'] = sprintf('%f', $run);

  //Join 1000 db Rows
    //insert 1000 rows into the db
    $run = -microtime(true);
    $x = 1000;
    for ($i=0; $i < $x ; $i++) {
      $rando = randomstring(255);
     $test =  $db->query("SELECT a.*, b.* FROM plg_benchmark a 
      LEFT OUTER JOIN plg_benchmark_join b ON a.randostring = b.randostring WHERE a.id = ?" ,[$i+1])->results();
 
     $test =  $db->query("SELECT 
          b.*, 
          a.*, 
          s.*, 
          a2.*, 
          e.*, 
          (b.id + a.id) AS sum,
          CONCAT(s.recap_public, ' - ', a.randostring) AS combined_info
      FROM 
          plg_benchmark a 
      JOIN 
          plg_benchmark_join b ON b.randostring = a.randostring 
      LEFT OUTER JOIN 
          settings s ON s.id = 1
      LEFT JOIN 
          `audit` a2 ON a2.id = 1
      LEFT OUTER JOIN 
          email e ON e.id = 1
      WHERE 
          a.id = ?
  ", [$i+1])->results();

    }
    $run += microtime(true);
    //dump("Insert 1000 DB Rows");
    //dump(sprintf('%f', $run));
    $data['join'] = sprintf('%f', $run);

  //Sum 1000 db Rows
  $run = -microtime(true);
  $blah = $db->query("SELECT sum(total) as data FROM plg_benchmark ORDER BY RAND()")->results();
  $run += microtime(true);
  //dump("Summed 1000 rows.");
  //dump($blah[0]->data);
  //dump(sprintf('%f', $run));
  $data['sum'] = sprintf('%f', $run);

  //Average 1000 db Rows
  $run = -microtime(true);
  $blah = $db->query("SELECT avg(total) as data FROM plg_benchmark ORDER BY RAND()")->results();
  $run += microtime(true);
  //dump("Average 1000 rows.");
  //dump($blah[0]->data);
  //dump(sprintf('%f', $run));
  $data['avg'] = sprintf('%f', $run);

  //MySQL Benchmark
  $run = -microtime(true);
  $blah = $db->query("SELECT BENCHMARK(1000000, AES_ENCRYPT('hello', UNHEX('F3229A0B371ED2D9441B830D21A390C3')));")->results();
  $run += microtime(true);
  //dump("MySQL Benchmark");
  //dump(sprintf('%f', $run));
  $data['sqlbench'] = sprintf('%f', $run);

  $db->query("TRUNCATE TABLE plg_benchmark");

  $db->query("TRUNCATE TABLE plg_benchmark_join");
  $count = 25;
    //math functions
    $run = -microtime(true);
    $time_start = microtime(true);
    $mathFunctions = array("abs", "acos", "asin", "atan", "bindec", "floor", "exp", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt");
    foreach ($mathFunctions as $key => $function) {
        if (!function_exists($function)) unset($mathFunctions[$key]);
    }
    for ($i = 0; $i < $count; $i++) {
        foreach ($mathFunctions as $function) {
       
           if($function == "pi"){
              $r = call_user_func_array($function, array());
            }elseif($function == "bindec"){
              $binaryString = decbin($i);
              $r = call_user_func_array($function, array($binaryString));
             }else{
              $r = call_user_func_array($function, array($i));
             } 
            
        }
    }
    $run += microtime(true);
    $data['math'] = sprintf('%f', $run);
    

  //string functions
  $run = -microtime(true);
  $stringFunctions = array("addslashes", "chunk_split", "metaphone", "strip_tags", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "soundex", "ord");
  $string = 'the quick brown fox jumps over the lazy dog';
  for ($i = 0; $i < $count; $i++) {
      foreach ($stringFunctions as $function) {
          call_user_func_array($function, array($string));
      }
  }
  $run += microtime(true);
  //dump("String functions");
  //dump(sprintf('%f', $run));
  $data['string'] = sprintf('%f', $run);

  //read 40,000 characters
  $run = -microtime(true);
  $read = $abs_us_root.$us_url_root.'usersc/plugins/benchmark/read.txt';
  $read = file_get_contents($read);
  $write = $abs_us_root.$us_url_root.'usersc/plugins/benchmark/write.txt';
  file_put_contents($write, $read);
  $run += microtime(true);
  //dump("Read/write 40,000 characters");
  //dump(sprintf('%f', $run));
  $data['readwrite'] = sprintf('%f', $run);
  $start += microtime(true);
  $data['time'] = sprintf('%f', $start);
  $score = round($data['time'],2);
  $score = $score * 100;
  //dump($score);
  //the swift average time is 6.70
    $diff = 670 - $score;
    $score = 1000 + $diff;

  //dump($score);
  $data['score'] = $score;
  $db->insert('plg_benchmark_saves',['benchname'=>Input::get('benchname'),'benchdata'=>json_encode($data)]);
  $results = true;
  file_put_contents($write, "");
  ?>



<?php }

if($results){ ?>
  <div class="row">
    <div class="col-6 offset-3" id="myTable">
      <h4 align="center">US Ver: <?=$user_spice_ver?> - PHP Ver: <?=PHP_VERSION?> - OS: <?=PHP_OS?></h4>
      <h3 align="center">Your Time: <?=$data['time'];?></h3>
      <h3 align="center">Official Score: <font color="red"> <?=$data['score'];?></font></h3>
      <p align="center">Benchmark Version 2.0.0</p>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Test</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Generate 10,000 Random Strings</td>
            <td><?=$data['random'];?></td>
          </tr>

          <tr>
            <td>Bcrypt a Password</td>
            <td><?=$data['bcrypt'];?></td>
          </tr>

          <tr>
            <td>Count to a Million</td>
            <td><?=$data['million'];?></td>
          </tr>

          <tr>
            <td>Insert 2000 DB Rows</td>
            <td><?=$data['insert'];?></td>
          </tr>

          <tr>
            <td>Join 2000 DB Rows</td>
            <td><?=$data['join'];?></td>
          </tr>

          <tr>
            <td>Sum 1000 DB Rows</td>
            <td><?=$data['sum'];?></td>
          </tr>

          <tr>
            <td>Average 1000 DB Rows</td>
            <td><?=$data['avg'];?></td>
          </tr>

          <tr>
            <td>MySQL Benchmark</td>
            <td><?=$data['sqlbench'];?></td>
          </tr>

          <tr>
            <td>Math Functions</td>
            <td><?=$data['math'];?></td>
          </tr>

          <tr>
            <td>String Functions</td>
            <td><?=$data['string'];?></td>
          </tr>

          <tr>
            <td>Read/Write 40k Characters</td>
            <td><?=$data['readwrite'];?></td>
          </tr>

        </tbody>
      </table>

    </div>
  </div>
<?php } ?>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
