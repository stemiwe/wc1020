<?php

require_once 'config.php';

// Reroute back.
if (!array_key_exists('game', $_SESSION)) {
    header('Location: /addgame.php');
    exit;
}

// Get params.
$player1 = $_SESSION['game']['player1'] ?? null;
$player2 = $_SESSION['game']['player2'] ?? null;
$player3 = $_SESSION['game']['player3'] ?? null;
$player4 = $_SESSION['game']['player4'] ?? null;
$wg = $_SESSION['game']['wg'] ?? null;
$lg = $_SESSION['game']['lg'] ?? null;
$elo_diff = $_SESSION['game']['elo_diff'] ?? null;

// Update DB on confirm.
if (array_key_exists('confirm', $_POST)) {    
    $DB->pdo->beginTransaction();

    try {
        
        // Get player IDs.
        $p1_id = $player1['id'];
        $p2_id = $player2['id'];
        $p3_id = $player3['id'];
        $p4_id = $player4['id'];
        
        // Get existing team.
        $t1_id = $DB->get("teams", "id", [
            "AND" => [
                "p1" => $p1_id,
                "p2" => $p2_id
            ]
        ]);
        
        // If team doesn't exist, create it.
        if (!$t1_id) {
            $DB->insert("teams", [
                'p1' => $p1_id,
                'p2' => $p2_id
            ]);
            $t1_id = $DB->id();
        }

        // Get or create Team 2.        
        $t2_id = $DB->get("teams", "id", [
            "AND" => [
                "p1" => $p3_id,
                "p2" => $p4_id  
            ]
        ]);
        
        if (!$t2_id) {
            $DB->insert("teams", [
                'p1' => $p3_id,
                'p2' => $p4_id
            ]);
            $t2_id = $DB->id();
        }

        // Create game record.
        $game = [
            'winner' => $t1_id,
            'loser' => $t2_id,
            'wg' => $wg,
            'lg' => $lg,
            'date' => date('Y-m-d'),
            'timestamp' => time(),
        ];        
        $DB->insert("games", $game);       
        
        // Update ELO for players.
        $elo1 = $player1['elo'] + $elo_diff;
        $elo2 = $player2['elo'] + $elo_diff;
        $elo3 = $player3['elo'] - $elo_diff;
        $elo4 = $player4['elo'] - $elo_diff;
        $DB->update("players", [
            'elo' => $elo1
        ], [
            'id' => $p1_id
        ]);
        $DB->update("players", [
            'elo' => $elo2
        ], [
            'id' => $p2_id
        ]);
        $DB->update("players", [
            'elo' => $elo3
        ], [
            'id' => $p3_id
        ]);
        $DB->update("players", [
            'elo' => $elo4
        ], [
            'id' => $p4_id
        ]);
        
        // Commit.        
        $DB->pdo->commit();
        header("Location: games.php");
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $DB->pdo->rollBack();
        $error = "Error saving game: " . $e->getMessage();
        // Show error to user or log it
    }

    // Clear session.
    unset($_SESSION['game']);
}


      
?>

<body>
    <h1>Confirm Game</h1>
    <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
    <form method="post">
        <input type="hidden" name="confirm" value="1"></input>
        <div class="form inputform confirm">

            <div class="form-card winner winner-confirm">
                <h2>Winner</h2>
                <div class="form-element">     
                    <div class="elo-confirm elo-winner">+<?= $elo_diff ?></div>     
                    <div>
                        <div class="player-cell"><?= write_player($player1) ?></div>                                             
                        <div class="player-cell"><?= write_player($player2) ?></div>
                    </div>               
                    <div class="goal-confirm"><?= $wg ?></div>                                 
                </div>                               
            </div>
            
            <div class="form-card loser loser-confirm">
                <div class="form-element">  
                    <div class="elo-confirm elo-loser">-<?= $elo_diff ?></div>  
                    <div>
                        <div class="player-cell"><?= write_player($player3) ?></div>                                 
                        <div class="player-cell"><?= write_player($player4) ?></div>
                    </div>                    
                    <div class="goal-confirm"><?= $lg ?></div>                                          
                </div>            
                <h2>Loser</h2>          
            </div>            
                
            <div class="footer">
                <button class="button" type="submit">OK</button>            
                <a href="/games.php" class="button">Cancel</a>     
            </div>
        </div>
    </form>
</body>
</html>