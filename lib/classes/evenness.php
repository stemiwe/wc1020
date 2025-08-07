<?php
/**
 * Class to compute evenness of game distribution among partners.
 *
 * Thanks chatGPT.
 */
class evenness {

    /**
     * Compute evenness of partner choice for a single player.
     *
     * @param array $partner_stats [partner_id => ['g' => int, ...]]
     * @param array $all_players   [partner_id => total_games_played]
     * @return float               Evenness score (0 = uneven, 1 = perfect)
     */
    public static function compute_evenness(array $partner_stats, array $all_players): float {
        $observed = [];
        $expected = [];

        // Total number of games played with all partners
        $sum_observed = array_sum(array_column($partner_stats, 'g'));

        // Total number of games played by all potential partners
        $sum_expected = array_sum($all_players);

        foreach ($all_players as $partner_id => $total_games) {
            $g = $partner_stats[$partner_id]['g'] ?? 0;

            $observed[$partner_id] = ($sum_observed > 0) ? $g / $sum_observed : 0;
            $expected[$partner_id] = ($sum_expected > 0) ? $total_games / $sum_expected : 0;
        }

        return 1 - self::jensen_shannon_divergence($observed, $expected);
    }

    private static function jensen_shannon_divergence(array $p, array $q): float {
        $allKeys = array_unique(array_merge(array_keys($p), array_keys($q)));
        $m = [];

        foreach ($allKeys as $key) {
            $pVal = $p[$key] ?? 0.0;
            $qVal = $q[$key] ?? 0.0;
            $m[$key] = 0.5 * ($pVal + $qVal);
        }

        return 0.5 * (self::kl_divergence($p, $m) + self::kl_divergence($q, $m));
    }

    private static function kl_divergence(array $p, array $q): float {
        $div = 0.0;
        foreach ($p as $key => $pVal) {
            if ($pVal > 0) {
                $qVal = $q[$key] ?? 1e-12;
                $div += $pVal * log($pVal / $qVal, 2);
            }
        }
        return $div;
    }
}