<?php
// ============================================================
// FarmersBD — Pagination Component
// ============================================================

/**
 * Render Bootstrap 5 pagination links.
 *
 * @param array  $pager      Result from paginate()
 * @param string $base_url   URL without page param
 * @param string $param      Query parameter name (default: 'page')
 */
function render_pagination(array $pager, string $base_url = '', string $param = 'page'): void {
    if ($pager['total_pages'] <= 1) return;

    if (empty($base_url)) {
        $base_url = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    }

    $current    = $pager['current_page'];
    $total      = $pager['total_pages'];
    $separator  = str_contains($base_url, '?') ? '&' : '?';

    echo '<nav aria-label="পেজিনেশন" class="mt-4">';
    echo '<ul class="pagination justify-content-center flex-wrap">';

    // Previous
    if ($pager['has_prev']) {
        $prev = $current - 1;
        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"{$base_url}{$separator}{$param}={$prev}\" aria-label=\"পূর্ববর্তী\">«</a></li>";
    } else {
        echo '<li class="page-item disabled"><span class="page-link">«</span></li>';
    }

    // Page numbers with ellipsis
    $range = 2;
    for ($i = 1; $i <= $total; $i++) {
        if ($i === 1 || $i === $total || ($i >= $current - $range && $i <= $current + $range)) {
            $active = $i === $current ? ' active' : '';
            echo "<li class=\"page-item{$active}\"><a class=\"page-link\" href=\"{$base_url}{$separator}{$param}={$i}\">{$i}</a></li>";
        } elseif ($i === $current - $range - 1 || $i === $current + $range + 1) {
            echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
    }

    // Next
    if ($pager['has_next']) {
        $next = $current + 1;
        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"{$base_url}{$separator}{$param}={$next}\" aria-label=\"পরবর্তী\">»</a></li>";
    } else {
        echo '<li class="page-item disabled"><span class="page-link">»</span></li>';
    }

    echo '</ul>';

    // Summary
    $from = $pager['offset'] + 1;
    $to   = min($pager['offset'] + $pager['per_page'], $pager['total']);
    echo "<p class=\"text-center text-muted small mt-1\">{$pager['total']} টির মধ্যে {$from}–{$to} দেখানো হচ্ছে</p>";

    echo '</nav>';
}
