<?php
// This file is meant to be included, not accessed directly
if(!isset($ruleset)) {
  $ruleset = null;
}

// Get plugin settings
$hfSettings = $db->query("SELECT * FROM plg_handfoot_settings WHERE id = 1")->first();
if(!$hfSettings) {
  // Create default settings if not exists
  $db->insert('plg_handfoot_settings', ['id' => 1, 'require_login' => 0, 'allow_user_creation' => 0]);
  $hfSettings = $db->query("SELECT * FROM plg_handfoot_settings WHERE id = 1")->first();
}

// Get all rulesets for selection
$allRulesets = $db->query("SELECT * FROM plg_handfoot_rulesets ORDER BY is_default DESC, name ASC")->results();

// Get ruleset info - check POST first for new game creation
$selectedRulesetId = isset($_POST['ruleset_id']) ? (int)$_POST['ruleset_id'] : $ruleset;

if($selectedRulesetId) {
  $rulesetInfo = $db->query("SELECT * FROM plg_handfoot_rulesets WHERE id = ?", array($selectedRulesetId))->first();
} else {
  $rulesetInfo = $db->query("SELECT * FROM plg_handfoot_rulesets WHERE is_default = 1")->first();
}

if(!$rulesetInfo && empty($allRulesets)) {
  echo '<div class="alert alert-danger">No ruleset found. Please configure rulesets first.</div>';
  return;
}

// If no default but rulesets exist, use first one
if(!$rulesetInfo && !empty($allRulesets)) {
  $rulesetInfo = $allRulesets[0];
}

// Get current IP
$ip = ipCheck();

// Handle form submissions
if(!empty($_POST)) {
  if(isset($_POST['action']) && $_POST['action'] == 'create_game') {
    // Create game
    $playerNames = [];
    $playerUserIds = [];
    $numPlayers = (int)$_POST['num_players'];
    $selectedRuleset = (int)Input::get('ruleset_id');

    // Collect player data - handle both user mode and text mode
    for($i = 1; $i <= $numPlayers; $i++) {
      if($hfSettings->require_login) {
        // User mode: get user_id and look up username for display
        $userId = (int)Input::get("player_user_id_{$i}");
        $playerUserIds[] = $userId;
        if($userId > 0) {
          $userData = $db->query("SELECT username, fname, lname FROM users WHERE id = ?", [$userId])->first();
          $playerNames[] = $userData ? ($userData->fname ?: $userData->username) : "Player $i";
        } else {
          $playerNames[] = "Player $i";
        }
      } else {
        // Text mode: just get the name
        $playerNames[] = Input::get("player{$i}");
        $playerUserIds[] = null;
      }
    }

    // Create the game
    $gameFields = array(
      'ruleset_id' => $selectedRuleset ? $selectedRuleset : $rulesetInfo->id,
      'num_players' => count($playerNames),
      'creator_ip' => $ip,
      'creator_user_id' => (isset($user) && $user->isLoggedIn()) ? $user->data()->id : null
    );
    $db->insert('plg_handfoot_games', $gameFields);
    $newGameId = $db->lastId();

    // Create players
    foreach($playerNames as $index => $name) {
      $fields = array(
        'game_id' => $newGameId,
        'user_id' => $playerUserIds[$index],
        'player_name' => $name,
        'player_order' => $index
      );
      $db->insert('plg_handfoot_players', $fields);
    }

    // Redirect to game with ID
    Redirect::to( '?game_id=' . $newGameId);
  }

  if(isset($_POST['action']) && $_POST['action'] == 'save_round') {
    $gameId = (int)$_POST['game_id'];
    $roundNumber = (int)$_POST['round_number'];
    $wentOutPlayer = Input::get('went_out_player');

    // Verify game exists
    $gameCheck = $db->query("SELECT id FROM plg_handfoot_games WHERE id = ?", array($gameId));
    if($gameCheck->count() > 0) {
      // Get players for this game
      $players = $db->query("SELECT * FROM plg_handfoot_players WHERE game_id = ? ORDER BY player_order", array($gameId))->results();

      // Delete existing scores for this round
      $db->query("DELETE FROM plg_handfoot_scores WHERE game_id = ? AND round_number = ?",
                 array($gameId, $roundNumber));

      // Insert new scores
      foreach($players as $player) {
        $blackBooks = (int)Input::get('black_books_' . $player->id);
        $redBooks = (int)Input::get('red_books_' . $player->id);
        $cardPoints = (int)Input::get('card_points_' . $player->id);

        // Calculate total
        $total = ($blackBooks * $rulesetInfo->black_book_points) +
                 ($redBooks * $rulesetInfo->red_book_points) +
                 $cardPoints;

        // Add going out bonus
        if($player->player_name === $wentOutPlayer) {
          $roundKey = 'going_out_round_' . $roundNumber;
          $total += $rulesetInfo->$roundKey;
        }

        $fields = array(
          'game_id' => $gameId,
          'player_id' => $player->id,
          'round_number' => $roundNumber,
          'went_out' => ($player->player_name === $wentOutPlayer ? 1 : 0),
          'black_books' => $blackBooks,
          'red_books' => $redBooks,
          'card_points' => $cardPoints,
          'total_points' => $total
        );
        $db->insert('plg_handfoot_scores', $fields);
      }
    }

    // Redirect back to game
    Redirect::to(currentPage() . '?game_id=' . $gameId);
  }

  if(isset($_POST['action']) && $_POST['action'] == 'new_game_same_players') {
    $gameId = (int)$_POST['game_id'];

    // Get existing players (including user_id)
    $existingPlayersData = $db->query("SELECT player_name, user_id FROM plg_handfoot_players WHERE game_id = ? ORDER BY player_order", array($gameId))->results();

    // Create new game
    $fields = array(
      'ruleset_id' => $rulesetInfo->id,
      'num_players' => count($existingPlayersData),
      'creator_ip' => $ip,
      'creator_user_id' => (isset($user) && $user->isLoggedIn()) ? $user->data()->id : null
    );
    $db->insert('plg_handfoot_games', $fields);
    $newGameId = $db->lastId();

    // Create players for new game (preserve user_id)
    foreach($existingPlayersData as $index => $player) {
      $fields = array(
        'game_id' => $newGameId,
        'user_id' => $player->user_id,
        'player_name' => $player->player_name,
        'player_order' => $index
      );
      $db->insert('plg_handfoot_players', $fields);
    }

    // Redirect to new game
    Redirect::to(currentPage() . '?game_id=' . $newGameId);
  }
}

// Check if we're loading an existing game
$gameId = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;
$existingGame = null;
$existingPlayers = [];
$existingScores = [];
$currentRound = 1;

if($gameId) {
  $existingGame = $db->query("SELECT * FROM plg_handfoot_games WHERE id = ?", array($gameId))->first();
  if($existingGame) {
    // Verify IP matches
    // if($existingGame->creator_ip !== $ip) {
    //   echo '<div class="alert alert-danger">Access denied. This game was created by a different user.</div>';
    //   return;
    // }

    $rulesetInfo = $db->query("SELECT * FROM plg_handfoot_rulesets WHERE id = ?", array($existingGame->ruleset_id))->first();
    $existingPlayers = $db->query("SELECT * FROM plg_handfoot_players WHERE game_id = ? ORDER BY player_order", array($gameId))->results();
    $existingScores = $db->query("SELECT * FROM plg_handfoot_scores WHERE game_id = ?", array($gameId))->results();

    // Find current round
    for($i = 1; $i <= 4; $i++) {
      $roundScores = array_filter($existingScores, function($s) use ($i) {
        return $s->round_number == $i;
      });
      if(empty($roundScores)) {
        $currentRound = $i;
        break;
      }
      if($i == 4) {
        $currentRound = 5; // Game complete
      }
    }
  }
}

// Calculate scores for display
$playerScores = [];
if(!empty($existingPlayers)) {
  foreach($existingPlayers as $player) {
    $playerScores[$player->id] = [
      'name' => $player->player_name,
      'rounds' => [null, null, null, null],
      'total' => 0,
      'details' => []
    ];
  }

  foreach($existingScores as $score) {
    if(isset($playerScores[$score->player_id])) {
      $playerScores[$score->player_id]['rounds'][$score->round_number - 1] = $score->total_points;
      $playerScores[$score->player_id]['total'] += $score->total_points;
      $playerScores[$score->player_id]['details'][$score->round_number - 1] = [
        'black_books' => $score->black_books,
        'red_books' => $score->red_books,
        'card_points' => $score->card_points,
        'went_out' => $score->went_out
      ];
    }
  }
}

// Find winner
$winningPlayers = [];
$maxScore = -PHP_INT_MAX;
foreach($playerScores as $pScore) {
  if($pScore['total'] > $maxScore) {
    $maxScore = $pScore['total'];
    $winningPlayers = [$pScore['name']];
  } elseif($pScore['total'] === $maxScore) {
    $winningPlayers[] = $pScore['name'];
  }
}

// Only highlight if score is greater than 0
if($maxScore <= 0) {
  $winningPlayers = [];
}

// Get editing round if set
$editingRound = isset($_GET['edit_round']) ? (int)$_GET['edit_round'] : null;
if($editingRound) {
  $currentRound = $editingRound;
}
?>

<?php if($hfSettings->require_login): ?>
<!-- Select2 CSS from CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<?php endif; ?>

<style>
.hf-winning-player {
  background-color: #28a745 !important;
  color: white !important;
  font-weight: bold;
}
.hf-edit-btn {
  cursor: pointer;
  text-decoration: underline;
  color: #0d6efd;
}
.hf-edit-btn:hover {
  color: #0a58ca;
}
.select2-container {
  width: 100% !important;
}
.hf-add-user-btn {
  margin-top: 5px;
}
</style>

<div class="col-12">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-start">
      <div>
        <h2 class="h4 mb-0">Hand and Foot Scorekeeper</h2>
        <small class="text-muted">Ruleset: <?= $rulesetInfo->name ?></small>
      </div>
      <a href="<?= $us_url_root ?>usersc/plugins/handfoot/stats.php" target="_blank" class="btn btn-sm btn-outline-success">
        <i class="fas fa-chart-bar"></i> View Statistics
      </a>
    </div>
    <div class="card-body">

      <!-- Game Setup -->
      <div id="gameSetup" <?= $existingGame ? 'style="display: none;"' : '' ?>>
        <h3 class="h5">Game Setup</h3>
        <form method="post">
          <input type="hidden" name="action" value="create_game">

          <?php if(count($allRulesets) > 1): ?>
          <div class="mb-3">
            <label for="rulesetId" class="form-label">Ruleset</label>
            <select class="form-select" id="rulesetId" name="ruleset_id">
              <?php foreach($allRulesets as $rs): ?>
                <option value="<?= $rs->id ?>" <?= $rs->is_default ? 'selected' : '' ?>>
                  <?= htmlspecialchars($rs->name) ?><?= $rs->is_default ? ' (Default)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php else: ?>
          <input type="hidden" name="ruleset_id" value="<?= $rulesetInfo->id ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label for="numPlayers" class="form-label">Number of Players</label>
            <select class="form-select" id="numPlayers" name="num_players" required>
              <option value="">Select...</option>
              <option value="2">2 Players</option>
              <option value="3">3 Players</option>
              <option value="4">4 Players</option>
              <option value="5">5 Players</option>
              <option value="6">6 Players</option>
              <option value="7">7 Players</option>
              <option value="8">8 Players</option>
              <option value="9">9 Players</option>
              <option value="10">10 Players</option>
              <option value="11">11 Players</option>
              <option value="12">12 Players</option>
            </select>
          </div>

          <div id="playerNames" style="display: none;">
            <h4 class="h6">Player Names</h4>
            <div id="playerNameInputs"></div>
            <button type="submit" class="btn btn-primary mt-3">Start Game</button>
          </div>
        </form>
      </div>

      <!-- Game Play -->
      <div id="gamePlay" <?= !$existingGame ? 'style="display: none;"' : '' ?>>

        <!-- Current Totals at Top -->
        <div class="mb-4">
          <div class="row mb-2">
            <div class="col">
              <h3 class="h5">Current Totals</h3>
            </div>
            <div class="col-auto">
              <a href="<?= $us_url_root . basename($_SERVER['PHP_SELF']) ?>" class="btn btn-sm btn-secondary">New Game</a>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <?php foreach($playerScores as $pScore): ?>
                    <th class="text-center"><?= htmlspecialchars($pScore['name']) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <?php foreach($playerScores as $pScore): ?>
                    <td class="text-center <?= in_array($pScore['name'], $winningPlayers) ? 'hf-winning-player' : '' ?>">
                      <strong><?= $pScore['total'] ?></strong>
                    </td>
                  <?php endforeach; ?>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <?php if($currentRound <= 4): ?>
          <div class="row mb-3">
            <div class="col">
              <h3 class="h5">Round <?= $currentRound ?> of 4</h3>
            </div>
          </div>

          <!-- Round Scoring -->
          <form method="post" id="roundForm">
            <input type="hidden" name="action" value="save_round">
            <input type="hidden" name="game_id" value="<?= $gameId ?>">
            <input type="hidden" name="round_number" value="<?= $currentRound ?>">

            <div class="alert alert-info">
              <strong>Step 1:</strong> Select who went out this round
            </div>

            <div class="mb-4">
              <label class="form-label">Who went out?</label>
              <select class="form-select" id="wentOut" name="went_out_player" required>
                <option value="">Select player...</option>
                <?php
                  $wentOutPlayer = null;
                  if($editingRound) {
                    foreach($playerScores as $pId => $pScore) {
                      if(isset($pScore['details'][$editingRound - 1]) && $pScore['details'][$editingRound - 1]['went_out']) {
                        $wentOutPlayer = $pScore['name'];
                        break;
                      }
                    }
                  }
                  foreach($existingPlayers as $player):
                ?>
                  <option value="<?= htmlspecialchars($player->player_name) ?>" <?= $wentOutPlayer === $player->player_name ? 'selected' : '' ?>>
                    <?= htmlspecialchars($player->player_name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div id="playerScoring">
              <!-- Will be populated by JavaScript when player is selected -->
            </div>

            <button type="submit" class="btn btn-primary" id="submitRound" disabled>Submit Round Scores</button>
          </form>
        <?php else: ?>
          <!-- Winner Display -->
          <div class="alert alert-success mt-4">
            <h3 class="h4">Game Over!</h3>
            <p class="h5">Winner: <strong><?= implode(' and ', $winningPlayers) ?></strong> with <strong><?= $maxScore ?></strong> points!</p>
            <div class="mt-3">
              <form method="post" style="display: inline;">
                <input type="hidden" name="action" value="new_game_same_players">
                <input type="hidden" name="game_id" value="<?= $gameId ?>">
                <button type="submit" class="btn btn-primary">New Game (Same Players)</button>
              </form>
              <a href="<?= $us_url_root . basename($_SERVER['PHP_SELF']) ?>" class="btn btn-secondary">New Game (Different Players)</a>
            </div>
          </div>
        <?php endif; ?>

        <!-- Full Scoreboard at Bottom -->
        <div class="mt-5">
          <h3 class="h5 mb-3">Full Scoreboard</h3>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th></th>
                  <?php foreach($playerScores as $pScore): ?>
                    <th class="text-center"><?= htmlspecialchars($pScore['name']) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php for($round = 1; $round <= 4; $round++): ?>
                  <tr>
                    <td>
                      <strong>Round <?= $round ?></strong>
                      <?php
                        $hasScore = false;
                        foreach($playerScores as $pScore) {
                          if($pScore['rounds'][$round - 1] !== null) {
                            $hasScore = true;
                            break;
                          }
                        }
                        if($hasScore):
                      ?>
                        <a href="<?= $us_url_root . basename($_SERVER['PHP_SELF']) ?>?game_id=<?= $gameId ?>&edit_round=<?= $round ?>" class="hf-edit-btn">(edit)</a>
                      <?php endif; ?>
                    </td>
                    <?php foreach($playerScores as $pScore): ?>
                      <td class="text-center">
                        <?= $pScore['rounds'][$round - 1] !== null ? $pScore['rounds'][$round - 1] : '-' ?>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endfor; ?>
                <tr>
                  <td><strong>Total</strong></td>
                  <?php foreach($playerScores as $pScore): ?>
                    <td class="text-center <?= in_array($pScore['name'], $winningPlayers) ? 'hf-winning-player' : '' ?>">
                      <strong><?= $pScore['total'] ?></strong>
                    </td>
                  <?php endforeach; ?>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- Spacer to prevent page jumping -->
<div style="height: 200px;"></div>

<?php if($hfSettings->require_login): ?>
<!-- Select2 JS from CDN -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<?php endif; ?>

<?php if($hfSettings->require_login && $hfSettings->allow_user_creation): ?>
<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add New Player</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="addUserError" class="alert alert-danger" style="display: none;"></div>
        <div class="row">
          <div class="col-6">
            <div class="mb-3">
              <label for="newFname" class="form-label">First Name</label>
              <input type="text" class="form-control" id="newFname" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label for="newLname" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="newLname" required>
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label for="newEmail" class="form-label">Email</label>
          <input type="email" class="form-control" id="newEmail" required>
         
        </div>
        <input type="hidden" id="addUserForPlayer" value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createUserBtn">Create User</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
const rulesetInfo = <?= json_encode($rulesetInfo) ?>;
const playerData = <?= json_encode(array_values($existingPlayers)) ?>;
const playerScores = <?= json_encode($playerScores) ?>;
const currentRound = <?= $currentRound ?>;
const editingRound = <?= $editingRound ? $editingRound : 'null' ?>;
const userMode = <?= $hfSettings->require_login ? 'true' : 'false' ?>;
const allowUserCreation = <?= $hfSettings->allow_user_creation ? 'true' : 'false' ?>;
const csrfToken = '<?= Token::generate() ?>';
const parserUrl = '<?= $us_url_root ?>usersc/plugins/handfoot/parsers/users.php';

$(document).ready(function() {

  // Show player name inputs when number selected
  $('#numPlayers').on('change', function() {
    const numPlayers = parseInt($(this).val());
    if(numPlayers > 0) {
      let html = '';
      for(let i = 1; i <= numPlayers; i++) {
        if(userMode) {
          // User mode: Select2 dropdown
          html += `
            <div class="mb-3">
              <label for="player_select_${i}" class="form-label">Player ${i}</label>
              <select class="form-select player-select" id="player_select_${i}" name="player_user_id_${i}" data-player-num="${i}" required>
                <option value="">Search for a user...</option>
              </select>
              ${allowUserCreation ? `<button type="button" class="btn btn-sm btn-outline-secondary hf-add-user-btn" data-player-num="${i}">+ Add New User</button>` : ''}
            </div>
          `;
        } else {
          // Text mode: simple text input
          html += `
            <div class="mb-2">
              <label for="player${i}" class="form-label">Player ${i} Name</label>
              <input type="text" class="form-control" id="player${i}" name="player${i}" required>
            </div>
          `;
        }
      }
      $('#playerNameInputs').html(html);
      $('#playerNames').show();

      // Initialize Select2 for user mode
      if(userMode) {
        initSelect2();
      }
    } else {
      $('#playerNames').hide();
    }
  });

  // Initialize Select2 dropdowns
  function initSelect2() {
    $('.player-select').each(function() {
      $(this).select2({
        theme: 'bootstrap-5',
        placeholder: 'Search for a user...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
          url: parserUrl,
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              action: 'search_users',
              term: params.term,
              token: csrfToken
            };
          },
          processResults: function(data) {
            return {
              results: data.results || []
            };
          },
          cache: true
        }
      });
    });
  }

  // Handle add user button click
  $(document).on('click', '.hf-add-user-btn', function() {
    const playerNum = $(this).data('player-num');
    $('#addUserForPlayer').val(playerNum);
    $('#newFname').val('');
    $('#newLname').val('');
    $('#newEmail').val('');
    $('#addUserError').hide();
    $('#addUserModal').modal('show');
  });

  // Handle create user button
  $('#createUserBtn').on('click', function() {
    const fname = $('#newFname').val().trim();
    const lname = $('#newLname').val().trim();
    const email = $('#newEmail').val().trim();
    const playerNum = $('#addUserForPlayer').val();

    if(!fname || !lname || !email) {
      $('#addUserError').text('Please fill in all fields').show();
      return;
    }

    $(this).prop('disabled', true).text('Creating...');

    $.ajax({
      url: parserUrl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'create_user',
        fname: fname,
        lname: lname,
        email: email,
        token: csrfToken
      },
      success: function(response) {
        $('#createUserBtn').prop('disabled', false).text('Create User');

        if(response.success) {
          // Add the new user to the select and select it
          const newOption = new Option(response.user.text, response.user.id, true, true);
          $(`#player_select_${playerNum}`).append(newOption).trigger('change');
          $('#addUserModal').modal('hide');
        } else {
          $('#addUserError').text(response.message).show();
        }
      },
      error: function() {
        $('#createUserBtn').prop('disabled', false).text('Create User');
        $('#addUserError').text('An error occurred. Please try again.').show();
      }
    });
  });

  // When someone went out is selected
  $('#wentOut').on('change', function() {
    const wentOutPlayer = $(this).val();

    if(wentOutPlayer === '') {
      $('#playerScoring').html('');
      $('#submitRound').prop('disabled', true);
      return;
    }

    const roundNum = editingRound || currentRound;

    // Show scoring inputs for all players
    let html = '<div class="alert alert-info"><strong>Step 2:</strong> Enter books and card points for each player</div>';

    playerData.forEach(player => {
      // Get existing data if editing
      let blackBooks = '';
      let redBooks = '';
      let cardPoints = '';

      if(playerScores[player.id] && playerScores[player.id].details && playerScores[player.id].details[roundNum - 1]) {
        const details = playerScores[player.id].details[roundNum - 1];
        blackBooks = details.black_books;
        redBooks = details.red_books;
        cardPoints = details.card_points;
      }

      html += `
        <div class="card mb-3">
          <div class="card-header">
            <strong>${player.player_name}</strong>
            ${player.player_name === wentOutPlayer ? '<span class="badge bg-success">Went Out</span>' : ''}
          </div>
          <div class="card-body">
            <div class="row">
                         <div class="col-6 col-md-4 mb-2">
                <label class="form-label">Red Books</label>
                <input type="number" class="form-control book-input" name="red_books_${player.id}"
                       data-player="${player.player_name}" value="${redBooks}" min="0" placeholder="0">
              </div>
              <div class="col-6 col-md-4 mb-2">
                <label class="form-label">Black Books</label>
                <input type="number" class="form-control book-input" name="black_books_${player.id}"
                       data-player="${player.player_name}" value="${blackBooks}" min="0" placeholder="0">
              </div>
 
              <div class="col-12 col-md-4 mb-2">
                <label class="form-label">Card Points</label>
                <input type="number" class="form-control book-input" name="card_points_${player.id}"
                       data-player="${player.player_name}" value="${cardPoints}" placeholder="0">
                <small class="text-muted">Can be negative (but that would suck)</small>
              </div>
            </div>
            <div class="mt-2">
              <strong>Round Total: <span id="roundTotal_${player.id}">0</span></strong>
            </div>
          </div>
        </div>
      `;
    });

    $('#playerScoring').html(html);
    $('#submitRound').prop('disabled', false);

    // Add change listeners to calculate round totals
    $('.book-input').on('input', function() {
      calculateRoundTotals(wentOutPlayer);
    });

    // Calculate initial totals
    calculateRoundTotals(wentOutPlayer);
  });

  // Calculate round totals
  function calculateRoundTotals(wentOutPlayer) {
    const roundNum = editingRound || currentRound;

    playerData.forEach(player => {
      const blackBooks = parseInt($(`input[name="black_books_${player.id}"]`).val()) || 0;
      const redBooks = parseInt($(`input[name="red_books_${player.id}"]`).val()) || 0;
      const cardPoints = parseInt($(`input[name="card_points_${player.id}"]`).val()) || 0;

      let total = 0;
      total += blackBooks * rulesetInfo.black_book_points;
      total += redBooks * rulesetInfo.red_book_points;
      total += cardPoints;

      // Add going out bonus if applicable
      if(player.player_name === wentOutPlayer) {
        const roundKey = `going_out_round_${roundNum}`;
        total += rulesetInfo[roundKey];
      }

      $(`#roundTotal_${player.id}`).text(total);
    });
  }

  // Trigger change if editing
  <?php if($editingRound): ?>
  $('#wentOut').trigger('change');
  <?php endif; ?>

  // Form validation
  $('#roundForm').on('submit', function(e) {
    const wentOutPlayer = $('#wentOut').val();
    if(wentOutPlayer === '') {
      e.preventDefault();
      alert('Please select who went out');
      return false;
    }

    // Check if all fields are filled
    let allFilled = true;
    let missingPlayers = [];

    playerData.forEach(player => {
      const blackBooks = $(`input[name="black_books_${player.id}"]`).val();
      const redBooks = $(`input[name="red_books_${player.id}"]`).val();
      const cardPoints = $(`input[name="card_points_${player.id}"]`).val();

      if(blackBooks === '' || redBooks === '' || cardPoints === '') {
        allFilled = false;
        missingPlayers.push(player.player_name);
      }
    });

    if(!allFilled) {
      e.preventDefault();
      let message = 'The following players are missing data:\n';
      message += missingPlayers.join('\n');
      message += '\n\nDo you want to continue anyway?';

      if(confirm(message)) {
        this.submit();
      }
      return false;
    }
  });

});
</script>
