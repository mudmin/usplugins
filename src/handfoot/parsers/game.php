<?php
require_once __DIR__ . '/../../../../users/init.php';

$db = DB::getInstance();
$response = ['success' => false, 'message' => ''];

$action = Input::get('action');
$token = Input::get('token');

// Token check
if (!Token::check($token)) {
  $response['message'] = "Token mismatch";
  echo json_encode($response);
  die;
}

// Update game notes
if($action == 'update_notes') {
  $gameId = (int)Input::get('game_id');
  $notes = trim(Input::get('notes'));

  if($gameId < 1) {
    $response['message'] = "Invalid game ID";
    echo json_encode($response);
    die;
  }

  // Verify game exists
  $game = $db->query("SELECT * FROM plg_handfoot_games WHERE id = ?", [$gameId])->first();
  if(!$game) {
    $response['message'] = "Game not found";
    echo json_encode($response);
    die;
  }

  // Update notes
  $db->update('plg_handfoot_games', $gameId, ['notes' => $notes]);

  if(!$db->error()) {
    $response['success'] = true;
    $response['message'] = "Notes updated";
  } else {
    $response['message'] = "Error updating notes";
  }

  echo json_encode($response);
  die;
}

// Unknown action
$response['message'] = "Unknown action";
echo json_encode($response);
