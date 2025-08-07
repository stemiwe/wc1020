<?php
require_once __DIR__ . '/../lib/config.php';
echo html::menu();

// Save submenu selection.
$_SESSION['stats'] = basename(__FILE__);

// Hardcode filter for season.
$filter = [
  "options" => [1],
  "default" => 1,
  "sql" => "WHERE season = '1'",
  "col" => "season",
  "filter" => "season"
];
$usefilter = true;
echo html::filter($filter);

// ------------------- Stats -------------------
echo '<div class="player-section">';

// ------------------- Awards -------------------
echo '<div class="player-section">';
echo '<div class="season-subheader">Awards</div>';

// Build SQL.
$sql = "SELECT * FROM awards ";
if ($usefilter) {
    $sql .= $filter['sql'];
}
$sql .= " ORDER BY weight ASC";

// Get awards.
$awards = $DB->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo '<div class="award-container">';
foreach ($awards as $award) {
    unset ($award['season']);
    echo html::award($award);
}
echo '</div>';

echo html::footer();
