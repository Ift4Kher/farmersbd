<?php
// ============================================================
// FarmersBD — SEO / Meta Tag Helper
// ============================================================

/**
 * Render all SEO meta tags for a page.
 * 
 * @param array $page Override values for this page:
 *   title, description, keywords, og_title, og_description, og_image, canonical
 */
function render_seo(array $page = []): void {
    $site_name = setting('site_name', APP_NAME);
    $title     = $page['title']       ?? setting('meta_title', $site_name);
    $desc      = $page['description'] ?? setting('meta_description', '');
    $keywords  = $page['keywords']    ?? setting('meta_keywords', '');
    $og_title  = $page['og_title']    ?? $title;
    $og_desc   = $page['og_description'] ?? $desc;
    $og_image  = $page['og_image']    ?? setting('og_image', '');
    $canonical = $page['canonical']   ?? current_url();

    if ($og_image && !str_starts_with($og_image, 'http')) {
        $og_image = BASE_URL . '/' . ltrim($og_image, '/');
    }

    echo '<title>' . e($title) . '</title>' . "\n";
    echo '<meta name="description" content="' . e($desc) . '">' . "\n";
    if ($keywords) {
        echo '<meta name="keywords" content="' . e($keywords) . '">' . "\n";
    }
    echo '<link rel="canonical" href="' . e($canonical) . '">' . "\n";
    echo '<meta property="og:type"        content="website">' . "\n";
    echo '<meta property="og:title"       content="' . e($og_title) . '">' . "\n";
    echo '<meta property="og:description" content="' . e($og_desc) . '">' . "\n";
    echo '<meta property="og:url"         content="' . e($canonical) . '">' . "\n";
    if ($og_image) {
        echo '<meta property="og:image"   content="' . e($og_image) . '">' . "\n";
    }
    echo '<meta property="og:site_name"   content="' . e($site_name) . '">' . "\n";
    echo '<meta name="twitter:card"       content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title"      content="' . e($og_title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . e($og_desc) . '">' . "\n";
}
