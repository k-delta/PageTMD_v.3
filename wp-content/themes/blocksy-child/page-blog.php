<?php
/**
 * Página editorial del blog.
 */
get_header();

$posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);
$featured = tmd_blog_featured_post($posts);
$secondary = $featured ? tmd_blog_secondary_posts($posts, $featured->ID) : [];
$used = $featured ? [$featured->ID] : [];
$used = array_merge($used, wp_list_pluck($secondary, 'ID'));
$recent = array_values(array_filter($posts, static fn($post) => ! in_array($post->ID, $used, true)));
?>
<main class="tmd-blog-page" id="main">
    <header class="tmd-blog-intro">
        <div class="tmd-blog-container">
            <span class="tmd-blog-eyebrow">Conocimiento para tu operación</span>
            <h1>Blog y novedades</h1>
            <p>Consejos técnicos, tendencias y experiencias para mantener tu operación productiva, segura y en movimiento.</p>
        </div>
    </header>

    <div class="tmd-blog-container">
        <nav class="tmd-blog-filters" aria-label="Filtrar artículos por categoría">
            <button class="is-active" type="button" data-blog-filter="all">Todos</button>
            <?php foreach (tmd_blog_category_map() as $slug => $name) : ?>
                <button type="button" data-blog-filter="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></button>
            <?php endforeach; ?>
        </nav>

        <?php if ($featured) :
            $cats = tmd_blog_post_categories($featured->ID);
            $image = tmd_blog_image($featured->ID, 'large');
        ?>
            <article class="tmd-blog-hero" data-blog-item data-categories="<?php echo esc_attr(implode(' ', $cats['slugs'])); ?>">
                <a class="tmd-blog-hero-image<?php echo $image ? '' : ' tmd-blog-image-placeholder'; ?>" href="<?php echo esc_url(get_permalink($featured)); ?>">
                    <?php if ($image) : ?><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_the_title($featured)); ?>"><?php endif; ?>
                </a>
                <div class="tmd-blog-hero-panel">
                    <span><?php echo esc_html($cats['name']); ?> · <?php echo esc_html(get_the_date('j M Y', $featured)); ?></span>
                    <h2><?php echo esc_html(get_the_title($featured)); ?></h2>
                    <p><?php echo esc_html(tmd_blog_excerpt($featured->ID, 26)); ?></p>
                    <a class="tmd-blog-outline-btn" href="<?php echo esc_url(get_permalink($featured)); ?>">Leer artículo</a>
                </div>
            </article>

            <?php if ($secondary) : ?>
                <section class="tmd-blog-feature-grid" aria-label="Artículos seleccionados">
                    <?php foreach ($secondary as $post) { tmd_render_blog_card($post); } ?>
                </section>
            <?php endif; ?>

            <?php if ($recent) : ?>
                <section class="tmd-blog-latest" aria-labelledby="tmd-latest-title">
                    <h2 id="tmd-latest-title">Últimos artículos</h2>
                    <div class="tmd-blog-mini-grid">
                        <?php foreach ($recent as $post) { tmd_render_blog_mini_card($post); } ?>
                    </div>
                </section>
            <?php endif; ?>
            <p class="tmd-blog-empty-filter" data-blog-empty hidden>No hay artículos en esta categoría todavía.</p>
        <?php else : ?>
            <div class="tmd-blog-empty"><h2>Contenido en preparación</h2><p>Pronto encontrarás consejos y novedades para tu operación.</p></div>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
