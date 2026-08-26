<?php
/**
 * Artículo individual del blog.
 */
require_once get_stylesheet_directory() . '/inc/tmd-brand-consistency.php';
tmd_use_brand_consistency();

get_header();
the_post();
$post_id = get_the_ID();
$cats = tmd_blog_post_categories($post_id);
$image = tmd_blog_image($post_id, 'full');
$related = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => 3,
    'post__not_in' => [$post_id],
    'category__in' => wp_get_post_categories($post_id),
]);
if (count($related) < 3) {
    $fallback = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 3 - count($related),
        'post__not_in' => array_merge([$post_id], wp_list_pluck($related, 'ID')),
    ]);
    $related = array_merge($related, $fallback);
}
$share_url = rawurlencode(get_permalink());
$share_title = rawurlencode(get_the_title());
?>
<main class="tmd-article-page" id="main">
    <div class="tmd-article-container">
        <nav class="tmd-blog-breadcrumb" aria-label="Migas de pan">
            <a href="<?php echo esc_url(home_url('/nosotros/')); ?>">Nosotros</a><span>›</span>
            <a href="<?php echo esc_url(tmd_blog_url()); ?>">Blog</a><span>›</span>
            <span><?php the_title(); ?></span>
        </nav>
        <header class="tmd-article-header">
            <span class="tmd-blog-category"><?php echo esc_html($cats['name']); ?></span>
            <h1><?php the_title(); ?></h1>
            <p class="tmd-article-meta"><?php echo esc_html(get_the_date('j \d\e F \d\e Y')); ?> · <?php echo esc_html(tmd_blog_reading_time($post_id)); ?> min de lectura</p>
        </header>
        <?php if ($image) : ?>
            <figure class="tmd-article-cover"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"></figure>
        <?php endif; ?>
        <div class="tmd-article-layout">
            <aside class="tmd-article-share" aria-label="Compartir artículo">
                <span>Compartir</span>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo esc_attr($share_url); ?>" target="_blank" rel="noopener" aria-label="Compartir en LinkedIn">in</a>
                <a href="https://wa.me/?text=<?php echo esc_attr($share_title . '%20' . $share_url); ?>" target="_blank" rel="noopener" aria-label="Compartir por WhatsApp"><i class="ti ti-brand-whatsapp"></i></a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr($share_url); ?>" target="_blank" rel="noopener" aria-label="Compartir en Facebook">f</a>
                <button type="button" data-copy-url="<?php echo esc_url(get_permalink()); ?>" aria-label="Copiar enlace"><i class="ti ti-link"></i></button>
            </aside>
            <article class="tmd-article-content"><?php the_content(); ?></article>
        </div>
        <section class="tmd-article-cta">
            <div><span>¿Necesitas apoyo especializado?</span><h2>Hablemos de tu operación</h2></div>
            <div><a href="<?php echo esc_url(home_url('/nosotros/contacto/')); ?>">Contáctanos</a><a class="is-outline" href="<?php echo esc_url(home_url('/equipos/')); ?>">Ver equipos</a></div>
        </section>
        <?php if ($related) : ?>
            <section class="tmd-blog-related" aria-labelledby="tmd-related-title">
                <h2 id="tmd-related-title">Artículos relacionados</h2>
                <div class="tmd-blog-mini-grid"><?php foreach ($related as $post) { tmd_render_blog_mini_card($post); } ?></div>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
