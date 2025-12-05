<?php
require_once "../../../users/init.php";
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';


// Get plugin settings
$hfSettings = $db->query("SELECT * FROM plg_handfoot_settings WHERE id = 1")->first();

// Determine which user's stats to show
$viewUserId = null;
$viewUserName = null;
$isOwnStats = false;

if(isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
  $viewUserId = (int)$_GET['user_id'];
  $viewUserData = $db->query("SELECT id, username, fname, lname FROM users WHERE id = ?", [$viewUserId])->first();
  if($viewUserData) {
    $viewUserName = $viewUserData->fname ? $viewUserData->fname . ' ' . $viewUserData->lname : $viewUserData->username;
  }
} elseif(isset($user) && $user->isLoggedIn()) {
  $viewUserId = $user->data()->id;
  $viewUserName = $user->data()->fname ? $user->data()->fname . ' ' . $user->data()->lname : $user->data()->username;
  $isOwnStats = true;
}

// Get IP for non-logged-in stats
$ip = ipCheck();

// Calculate stats
$stats = [];

if($viewUserId) {
  // User-based stats

  // Games played (as a player)
  $gamesPlayed = $db->query("
    SELECT COUNT(DISTINCT p.game_id) as count
    FROM plg_handfoot_players p
    JOIN plg_handfoot_games g ON p.game_id = g.id
    WHERE p.user_id = ?
  ", [$viewUserId])->first();
  $stats['games_played'] = $gamesPlayed->count ?? 0;

  // Games created
  $gamesCreated = $db->query("
    SELECT COUNT(*) as count
    FROM plg_handfoot_games
    WHERE creator_user_id = ?
  ", [$viewUserId])->first();
  $stats['games_created'] = $gamesCreated->count ?? 0;

  // Games won (player with highest score in completed games)
  $gamesWon = $db->query("
    SELECT COUNT(*) as wins FROM (
      SELECT g.id as game_id, p.user_id,
        (SELECT SUM(s2.total_points) FROM plg_handfoot_scores s2 WHERE s2.player_id = p.id) as total_score,
        (SELECT MAX(player_total) FROM (
          SELECT p2.id, SUM(s3.total_points) as player_total
          FROM plg_handfoot_players p2
          JOIN plg_handfoot_scores s3 ON p2.id = s3.player_id
          WHERE p2.game_id = g.id
          GROUP BY p2.id
        ) as max_scores) as max_score
      FROM plg_handfoot_games g
      JOIN plg_handfoot_players p ON g.id = p.game_id
      WHERE p.user_id = ?
      AND (SELECT COUNT(DISTINCT round_number) FROM plg_handfoot_scores WHERE game_id = g.id) = 4
      HAVING total_score = max_score AND total_score > 0
    ) as wins
  ", [$viewUserId])->first();
  $stats['games_won'] = $gamesWon->wins ?? 0;

  // Total points scored
  $totalPoints = $db->query("
    SELECT COALESCE(SUM(s.total_points), 0) as total
    FROM plg_handfoot_scores s
    JOIN plg_handfoot_players p ON s.player_id = p.id
    WHERE p.user_id = ?
  ", [$viewUserId])->first();
  $stats['total_points'] = $totalPoints->total ?? 0;

  // Total books
  $totalBooks = $db->query("
    SELECT COALESCE(SUM(s.black_books), 0) as black, COALESCE(SUM(s.red_books), 0) as red
    FROM plg_handfoot_scores s
    JOIN plg_handfoot_players p ON s.player_id = p.id
    WHERE p.user_id = ?
  ", [$viewUserId])->first();
  $stats['total_black_books'] = $totalBooks->black ?? 0;
  $stats['total_red_books'] = $totalBooks->red ?? 0;

  // Times went out
  $wentOut = $db->query("
    SELECT COUNT(*) as count
    FROM plg_handfoot_scores s
    JOIN plg_handfoot_players p ON s.player_id = p.id
    WHERE p.user_id = ? AND s.went_out = 1
  ", [$viewUserId])->first();
  $stats['times_went_out'] = $wentOut->count ?? 0;

  // Average score per game
  $avgScore = $db->query("
    SELECT AVG(game_total) as avg_score FROM (
      SELECT p.game_id, SUM(s.total_points) as game_total
      FROM plg_handfoot_scores s
      JOIN plg_handfoot_players p ON s.player_id = p.id
      WHERE p.user_id = ?
      GROUP BY p.game_id
    ) as game_scores
  ", [$viewUserId])->first();
  $stats['avg_score_per_game'] = round($avgScore->avg_score ?? 0);

  // Best single game score
  $bestGame = $db->query("
    SELECT p.game_id, SUM(s.total_points) as game_total
    FROM plg_handfoot_scores s
    JOIN plg_handfoot_players p ON s.player_id = p.id
    WHERE p.user_id = ?
    GROUP BY p.game_id
    ORDER BY game_total DESC
    LIMIT 1
  ", [$viewUserId])->first();
  $stats['best_game_score'] = $bestGame->game_total ?? 0;
  $stats['best_game_id'] = $bestGame->game_id ?? null;

  // Best single round score
  $bestRound = $db->query("
    SELECT s.total_points, s.round_number, p.game_id
    FROM plg_handfoot_scores s
    JOIN plg_handfoot_players p ON s.player_id = p.id
    WHERE p.user_id = ?
    ORDER BY s.total_points DESC
    LIMIT 1
  ", [$viewUserId])->first();
  $stats['best_round_score'] = $bestRound->total_points ?? 0;
  $stats['best_round_number'] = $bestRound->round_number ?? null;
  $stats['best_round_game_id'] = $bestRound->game_id ?? null;

  // Recent games
  $recentGames = $db->query("
    SELECT g.id, g.created_at, g.num_players,
      (SELECT SUM(s.total_points) FROM plg_handfoot_scores s WHERE s.player_id = p.id) as my_score,
      (SELECT MAX(s3.player_total) FROM (
        SELECT p2.game_id as gid, p2.id, SUM(s2.total_points) as player_total
        FROM plg_handfoot_players p2
        JOIN plg_handfoot_scores s2 ON p2.id = s2.player_id
        GROUP BY p2.game_id, p2.id
      ) as s3 WHERE s3.gid = g.id) as winning_score,
      (SELECT COUNT(DISTINCT round_number) FROM plg_handfoot_scores sc JOIN plg_handfoot_players pc ON sc.player_id = pc.id WHERE pc.game_id = g.id) as rounds_played
    FROM plg_handfoot_games g
    JOIN plg_handfoot_players p ON g.id = p.game_id
    WHERE p.user_id = ?
    ORDER BY g.created_at DESC
    LIMIT 10
  ", [$viewUserId])->results();

  // Head-to-head stats (who they play against most)
  $opponents = $db->query("
    SELECT p2.player_name, p2.user_id, COUNT(DISTINCT p2.game_id) as games_together,
      SUM(CASE WHEN (
        SELECT SUM(s1.total_points) FROM plg_handfoot_scores s1 WHERE s1.player_id = p1.id
      ) > (
        SELECT SUM(s2.total_points) FROM plg_handfoot_scores s2 WHERE s2.player_id = p2.id
      ) THEN 1 ELSE 0 END) as wins_against
    FROM plg_handfoot_players p1
    JOIN plg_handfoot_players p2 ON p1.game_id = p2.game_id AND p1.id != p2.id
    WHERE p1.user_id = ?
    GROUP BY p2.player_name, p2.user_id
    ORDER BY games_together DESC
    LIMIT 5
  ", [$viewUserId])->results();

} else {
  // IP-based stats for non-logged-in users (games they created)
  $gamesCreated = $db->query("
    SELECT COUNT(*) as count
    FROM plg_handfoot_games
    WHERE creator_ip = ?
  ", [$ip])->first();
  $stats['games_created'] = $gamesCreated->count ?? 0;
  $stats['games_played'] = 0;
  $stats['games_won'] = 0;

  $recentGames = $db->query("
    SELECT g.id, g.created_at, g.num_players,
      (SELECT COUNT(DISTINCT round_number) FROM plg_handfoot_scores WHERE game_id = g.id) as rounds_played
    FROM plg_handfoot_games g
    WHERE g.creator_ip = ?
    ORDER BY g.created_at DESC
    LIMIT 10
  ", [$ip])->results();

  $opponents = [];
}

// Full game history (all games for the user)
$allGames = [];
if($viewUserId) {
  $allGames = $db->query("
    SELECT g.id, g.created_at, g.num_players, g.notes,
      (SELECT SUM(s.total_points) FROM plg_handfoot_scores s WHERE s.player_id = p.id) as my_score,
      (SELECT MAX(s3.player_total) FROM (
        SELECT p2.game_id as gid, p2.id, SUM(s2.total_points) as player_total
        FROM plg_handfoot_players p2
        JOIN plg_handfoot_scores s2 ON p2.id = s2.player_id
        GROUP BY p2.game_id, p2.id
      ) as s3 WHERE s3.gid = g.id) as winning_score,
      (SELECT COUNT(DISTINCT sc.round_number) FROM plg_handfoot_scores sc JOIN plg_handfoot_players pc ON sc.player_id = pc.id WHERE pc.game_id = g.id) as rounds_played,
      (SELECT GROUP_CONCAT(p3.player_name ORDER BY p3.player_order SEPARATOR ', ')
       FROM plg_handfoot_players p3 WHERE p3.game_id = g.id) as all_players
    FROM plg_handfoot_games g
    JOIN plg_handfoot_players p ON g.id = p.game_id
    WHERE p.user_id = ?
    ORDER BY g.created_at DESC
  ", [$viewUserId])->results();
} else {
  $allGames = $db->query("
    SELECT g.id, g.created_at, g.num_players, g.notes,
      (SELECT COUNT(DISTINCT sc.round_number) FROM plg_handfoot_scores sc JOIN plg_handfoot_players pc ON sc.player_id = pc.id WHERE pc.game_id = g.id) as rounds_played,
      (SELECT GROUP_CONCAT(p.player_name ORDER BY p.player_order SEPARATOR ', ')
       FROM plg_handfoot_players p WHERE p.game_id = g.id) as all_players
    FROM plg_handfoot_games g
    WHERE g.creator_ip = ?
    ORDER BY g.created_at DESC
  ", [$ip])->results();
}

// Global leaderboard
$leaderboard = $db->query("
  SELECT p.user_id, p.player_name, u.username, u.fname, u.lname,
    COUNT(DISTINCT p.game_id) as games_played,
    COALESCE(SUM(s.total_points), 0) as total_points,
    COALESCE(SUM(s.black_books), 0) as total_black_books,
    COALESCE(SUM(s.red_books), 0) as total_red_books
  FROM plg_handfoot_players p
  LEFT JOIN users u ON p.user_id = u.id
  LEFT JOIN plg_handfoot_scores s ON p.id = s.player_id
  WHERE p.user_id IS NOT NULL
  GROUP BY p.user_id, p.player_name, u.username, u.fname, u.lname
  HAVING games_played >= 1
  ORDER BY total_points DESC
  LIMIT 10
")->results();

?>

  <div class="row mb-4">
    <div class="col">
      <h1>Hand and Foot Statistics</h1>
      <?php if($viewUserName): ?>
        <p class="lead"><?= $isOwnStats ? 'Your' : hed($viewUserName) . "'s" ?> Stats</p>
      <?php else: ?>
        <p class="lead">Games from this device</p>
      <?php endif; ?>
    </div>
    <div class="col-auto">
      <a href="<?= $us_url_root ?>usersc/plugins/handfoot/play_game.php" class="btn btn-primary">Play Game</a>
    </div>
  </div>

  <?php if($viewUserId): ?>
  <!-- User Stats Cards -->
  <div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
      <div class="card text-center h-100">
        <div class="card-body">
          <h2 class="display-4 text-primary"><?= number_format($stats['games_played']) ?></h2>
          <p class="card-text text-muted">Games Played</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card text-center h-100">
        <div class="card-body">
          <h2 class="display-4 text-success"><?= number_format($stats['games_won']) ?></h2>
          <p class="card-text text-muted">Games Won</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card text-center h-100">
        <div class="card-body">
          <h2 class="display-4 text-info"><?= number_format($stats['total_points']) ?></h2>
          <p class="card-text text-muted">Total Points</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card text-center h-100">
        <div class="card-body">
          <h2 class="display-4 text-warning"><?= number_format($stats['times_went_out']) ?></h2>
          <p class="card-text text-muted">Times Went Out</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Performance Stats</h5>
        </div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tr>
              <td>Win Rate</td>
              <td class="text-end fw-bold">
                <?= $stats['games_played'] > 0 ? round(($stats['games_won'] / $stats['games_played']) * 100) : 0 ?>%
              </td>
            </tr>
            <tr>
              <td>Avg Score per Game</td>
              <td class="text-end fw-bold"><?= number_format($stats['avg_score_per_game']) ?></td>
            </tr>
            <tr>
              <td>Best Game Score</td>
              <td class="text-end fw-bold">
                <?= number_format($stats['best_game_score']) ?>
                <?php if($stats['best_game_id']): ?>
                  <a href="<?= $us_url_root ?>usersc/plugins/handfoot/play_game.php?game_id=<?= $stats['best_game_id'] ?>" class="ms-1" title="View game"><i class="fas fa-external-link-alt"></i></a>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <td>Best Round Score</td>
              <td class="text-end fw-bold">
                <?= number_format($stats['best_round_score']) ?>
                <?php if($stats['best_round_number']): ?>
                  <small class="text-muted">(Round <?= $stats['best_round_number'] ?>)</small>
                <?php endif; ?>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Book Stats</h5>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-6">
              <div class="display-5"><?= number_format($stats['total_black_books']) ?></div>
              <p class="text-muted mb-0">Black Books</p>
              <small class="text-muted">(<?= number_format($stats['total_black_books'] * 300) ?> pts)</small>
            </div>
            <div class="col-6">
              <div class="display-5 text-danger"><?= number_format($stats['total_red_books']) ?></div>
              <p class="text-muted mb-0">Red Books</p>
              <small class="text-muted">(<?= number_format($stats['total_red_books'] * 500) ?> pts)</small>
            </div>
          </div>
          <hr>
          <div class="text-center">
            <div class="h4"><?= number_format($stats['total_black_books'] + $stats['total_red_books']) ?></div>
            <p class="text-muted mb-0">Total Books</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if(!empty($opponents)): ?>
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Frequent Opponents</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Player</th>
                  <th class="text-center">Games Together</th>
                  <th class="text-center">Your Wins</th>
                  <th class="text-center">Win Rate</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($opponents as $opp): ?>
                <tr>
                  <td>
                    <?= hed($opp->player_name) ?>
                    <?php if($opp->user_id): ?>
                      <a href="?user_id=<?= $opp->user_id ?>" class="ms-1"><i class="fas fa-chart-bar"></i></a>
                    <?php endif; ?>
                  </td>
                  <td class="text-center"><?= $opp->games_together ?></td>
                  <td class="text-center"><?= $opp->wins_against ?></td>
                  <td class="text-center">
                    <?= $opp->games_together > 0 ? round(($opp->wins_against / $opp->games_together) * 100) : 0 ?>%
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Recent Games -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Recent Games</h5>
        </div>
        <div class="card-body">
          <?php if(empty($recentGames)): ?>
            <p class="text-muted mb-0">No games found.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th class="text-center">Players</th>
                    <th class="text-center">Rounds</th>
                    <?php if($viewUserId): ?>
                    <th class="text-center">Your Score</th>
                    <th class="text-center">Result</th>
                    <?php endif; ?>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($recentGames as $game): ?>
                  <tr>
                    <td><?= date('M j, Y g:ia', strtotime($game->created_at)) ?></td>
                    <td class="text-center"><?= $game->num_players ?></td>
                    <td class="text-center">
                      <?= $game->rounds_played ?>/4
         
                    </td>
                    <?php if($viewUserId): ?>
                    <td class="text-center fw-bold"><?= number_format($game->my_score ?? 0) ?></td>
                    <td class="text-center">
                      <?php if($game->rounds_played == 4): ?>
                        <?php if($game->my_score == $game->winning_score): ?>
                          <span class="badge bg-success">Won</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Lost</span>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark">In Progress</span>
                      <?php endif; ?>
                    </td>
                    <?php endif; ?>
                    <td class="text-center">
                      <a href="<?= $us_url_root ?>usersc/plugins/handfoot/play_game.php?game_id=<?= $game->id ?>" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Leaderboard -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Leaderboard - Top Players</h5>
        </div>
        <div class="card-body">
          <?php if(empty($leaderboard)): ?>
            <p class="text-muted mb-0">No player data available yet.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th style="width: 50px;">#</th>
                    <th>Player</th>
                    <th class="text-center">Games</th>
                    <th class="text-center">Total Points</th>
                    <th class="text-center">Books</th>
                    <th class="text-center">Avg/Game</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($leaderboard as $i => $player): ?>
                  <tr <?= $viewUserId && $player->user_id == $viewUserId ? 'class="table-primary"' : '' ?>>
                    <td>
                      <?php if($i == 0): ?>
                        <span class="text-warning"><i class="fas fa-trophy"></i></span>
                      <?php elseif($i == 1): ?>
                        <span class="text-secondary"><i class="fas fa-medal"></i></span>
                      <?php elseif($i == 2): ?>
                        <span style="color: #cd7f32;"><i class="fas fa-medal"></i></span>
                      <?php else: ?>
                        <?= $i + 1 ?>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php
                        $displayName = $player->fname ? $player->fname . ' ' . $player->lname : ($player->username ?? $player->player_name);
                      ?>
                      <?= hed($displayName) ?>
                      <?php if($player->user_id): ?>
                        <a href="?user_id=<?= $player->user_id ?>" class="ms-1"><i class="fas fa-chart-bar"></i></a>
                      <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $player->games_played ?></td>
                    <td class="text-center fw-bold"><?= number_format($player->total_points) ?></td>
                    <td class="text-center">
                      <span title="Black Books"><?= $player->total_black_books ?></span> /
                      <span class="text-danger" title="Red Books"><?= $player->total_red_books ?></span>
                    </td>
                    <td class="text-center">
                      <?= $player->games_played > 0 ? number_format(round($player->total_points / $player->games_played)) : 0 ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Full Game History -->
  <?php if(!empty($allGames)): ?>
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Game History</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="gameHistoryTable">
              <thead>
                <tr>
                  <th class="text-start">Date</th>
                  <th>Players</th>
                  <th>Notes</th>
                  <th class="text-center">Rounds</th>
                  <?php if($viewUserId): ?>
                  <th class="text-center">Score</th>
                  <th class="text-center">Result</th>
                  <?php endif; ?>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($allGames as $game): ?>
                <tr>
                  <td class="text-start" data-order="<?= strtotime($game->created_at) ?>"><?= date('M j, Y g:ia', strtotime($game->created_at)) ?></td>
                  <td><?= hed($game->all_players) ?></td>
                  <td>
                    <?php if($game->notes): ?>
                      <small class="text-muted fst-italic"><?= hed($game->notes) ?></small>
                    <?php else: ?>
                      <small class="text-muted">-</small>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?= $game->rounds_played ?>/4

                  </td>
                  <?php if($viewUserId): ?>
                  <td class="text-center fw-bold" data-order="<?= $game->my_score ?? 0 ?>"><?= number_format($game->my_score ?? 0) ?></td>
                  <td class="text-center">
                    <?php if($game->rounds_played == 4): ?>
                      <?php if($game->my_score == $game->winning_score): ?>
                        <span class="badge bg-success">Won</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Lost</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">In Progress</span>
                    <?php endif; ?>
                  </td>
                  <?php endif; ?>
                  <td class="text-center">
                    <a href="<?= $us_url_root ?>usersc/plugins/handfoot/play_game.php?game_id=<?= $game->id ?>" class="btn btn-sm btn-outline-primary">View</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>
<link rel="stylesheet" href="<?= $us_url_root ?>users/js/pagination/datatables.min.css">
<script src="<?= $us_url_root ?>users/js/pagination/datatables.min.js"></script>
<script>
$(document).ready(function() {
  $('#gameHistoryTable').DataTable({
    "pageLength": 25,
    "order": [[0, "desc"]],
    "aLengthMenu": [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"]
    ]
  });
});
</script>

<?php
require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; 
