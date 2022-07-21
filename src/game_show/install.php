<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";



//all actions should be performed here.
$check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($check > 0){
	err($plugin_name.' has already been installed!');
}else{
 $fields = array(
	 'plugin'=>$plugin_name,
	 'status'=>'installed',
 );
 $db->insert('us_plugins',$fields);
 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins",$plugin_name." installed");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}

//do you want to inject your plugin in the middle of core UserSpice pages?
//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
$hooks = [];

if(!file_exists($abs_us_root.$us_url_root."game.php")){
	copy($abs_us_root.$us_url_root."usersc/plugins/game_show/files/game.php",$abs_us_root.$us_url_root."game.php");
}

$db->query("CREATE TABLE `gameshow_buzzers` (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `owner` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `buzzer_name` varchar(255) NOT NULL,
  `buzzer_key` varchar(255) NOT NULL,
  `light_color` varchar(255) NOT NULL,
  `sound` varchar(255) NOT NULL,
  `screen_color` varchar(255) DEFAULT NULL,
  `font_color` varchar(255) DEFAULT '#000',
  `disabled` tinyint(1) DEFAULT 0,
  `can_buzz` tinyint(1) DEFAULT 0,
  `buzzed` tinyint(1) DEFAULT 0,
  `elapsed` float(11,2) DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `to_play` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gameshow_buzzers`
--

INSERT INTO `gameshow_buzzers` (`id`, `owner`, `buzzer_name`, `buzzer_key`, `light_color`, `sound`, `screen_color`, `font_color`, `disabled`, `can_buzz`, `buzzed`, `elapsed`, `score`, `to_play`) VALUES
(1, 1, 'Player 1', '1234', 'blue', 'beep', '#04e748', '#000000', 0, 1, 0, 0.00, 0, ''),
(2, 1, 'Player 2', '1234', 'blue', 'beep', '#8100bd', '#ffffff', 0, 1, 0, 0.00, 0, ''),
(3, 1, 'Player 3', '1234', 'blue', 'beep', '#ede502', '#000000', 0, 1, 0, 0.00, 0, ''),
(4, 1, 'Player 4', '1234', 'blue', 'beep', '#02f2ca', '#000000', 0, 1, 0, 0.00, 0, ''),
(5, 1, 'Player 5', '1234', 'blue', 'beep', '#ea10b0', '#000000', 0, 1, 0, 0.00, 0, ''),
(6, 1, 'Player 6', '1234', 'blue', 'beep', '#ef0b0b', '#000000', 0, 1, 0, 0.00, 0, ''),
(7, 1, 'Player 7', '1234', 'blue', 'beep', '#1e24dc', '#ffffff', 0, 1, 0, 0.00, 0, ''),
(8, 1, 'Player 8', '1234', 'blue', 'beep', '#ff8800', '#000000', 0, 1, 0, 0.00, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `gameshow_light_colors`
--

CREATE TABLE `gameshow_light_colors` (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `color` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gameshow_light_colors`
--

INSERT INTO `gameshow_light_colors` (`id`, `color`) VALUES
(1, 'blue'),
(2, 'red'),
(3, 'pink'),
(4, 'green'),
(5, 'orange'),
(6, 'yellow'),
(7, 'purple'),
(8, 'white');

-- --------------------------------------------------------

--
-- Table structure for table `gameshow_settings`
--

CREATE TABLE `gameshow_settings` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `owner` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `require_key` tinyint(1) DEFAULT 1,
  `play_sounds` tinyint(1) DEFAULT 0,
  `begin_time` varchar(100) NOT NULL,
  `game` int(11) NOT NULL DEFAULT 1,
  `live_url` varchar(255) NOT NULL default 'https://openbuzzer.com/',
  `disabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `gameshow_settings`
--

INSERT INTO `gameshow_settings` (`id`, `owner`, `require_key`, `play_sounds`, `begin_time`, `game`, `disabled`) VALUES
(1, 1, 1, 1, '2022-06-22 11:50:08.706400', 3, '', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gameshow_buzzers`
--
ALTER TABLE `gameshow_buzzers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gameshow_light_colors`
--
ALTER TABLE `gameshow_light_colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gameshow_settings`
--
ALTER TABLE `gameshow_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gameshow_buzzers`
--
ALTER TABLE `gameshow_buzzers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `gameshow_light_colors`
--
ALTER TABLE `gameshow_light_colors`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `gameshow_settings`
--
ALTER TABLE `gameshow_settings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

$hooks = [];
$hooks['account.php']['body'] = 'hooks/accountbody.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
