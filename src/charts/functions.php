<?php
//Please don't load functions system-wide if you don't need them system-wide.
// To make your plugin more efficient on resources, consider only loading resources that need to be loaded when they need to be loaded.
// For instance, you can do
// $currentPage = currentPage();
// if($currentPage == 'admin.php'){ //The administrative dashboard
//   bold("<br>See! I am only loading this when I need it!");
// }
// // Also, please wrap your functions in if(!function_exists())
// if(!function_exists('chartsFunction')) {
//   function chartsFunction(){ }
// }

function createChart($data,$opts=[]){
  $db = DB::getInstance();
//example $data = $db->query("SELECT username, logins FROM users LIMIT 10")->results();
if(!array_key_exists('id',$opts)){$opts['id'] = "x".uniqid();}
if(!array_key_exists('type',$opts)){$opts['type'] = 'pie';}
if(!array_key_exists('title',$opts)){$opts['title'] = "";}
if(!array_key_exists('nolegend',$opts)){$opts['nolegend'] = "";}
if($opts['type']=='pie' && $opts['nolegend'] != true){$legend = 'true';}else{$legend = 'false';}
$vars = get_object_vars($data[0]);
$var = [];
foreach($vars as $k=>$v){
  $var[]=$k;
}

$labels = [];
$values = [];

foreach($data as $d){
  $d = (array)$d;
  $labels[] = "\"".ucfirst($d[$var[0]])."\"";
  $values[] = $d[$var[1]];
}
$count = count($labels);
$colors = $db->query("SELECT color FROM plg_charts_colors LIMIT $count")->results();
$clr = [];
foreach($colors as $c){
    $clr[] = "\"".$c->color."\"";
}
$colors = implode(', ',$clr);
$labels = implode(', ',$labels);
$values = implode(', ',$values);

   ?>
  <canvas id="<?=$opts['id']?>" height="<?=$opts['height']?>" width="<?=$opts['height']?>"></canvas>
  <script type="text/javascript">
  new Chart(document.getElementById("<?=$opts['id']?>"), {
  	type: "<?=$opts['type']?>",
  	data: {
  		labels: [<?=$labels?>],
  		datasets: [{
  			backgroundColor: [<?=$colors?>],
  			data: [<?=$values?>]
  		}]
  	},
  	options: {
      maintainAspectRatio: false,
      legend: { display: <?=$legend?> },
  		title: {
  			display: true,
  			text: "<?=$opts['title']?>"
  		},
      <?php if($opts['type']=='bar' || $opts['type']=='line'){?>
      scales: {
    yAxes: [{
        ticks: {
            beginAtZero: true
        }
    }]
},
<?php } ?>
  	}
  });
  </script>
  <?php
}
