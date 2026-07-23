<?php
/**
 * Archive (Category / Tag / Date / Author) – Cozy Fandom Design
 *
 * @package cozy-fandom-child
 */

get_header();

$queried   = get_queried_object();
$is_cat    = is_category();
$is_tag    = is_tag();
$is_author = is_author();
$is_date   = is_date();

if ( $is_cat ) {
    $archive_title = single_cat_title( '', false );
    $archive_desc  = category_description();
    $badge_label   = 'Categoría';
} elseif ( $is_tag ) {
    $archive_title = single_tag_title( '', false );
    $archive_desc  = tag_description();
    $badge_label   = 'Etiqueta';
} elseif ( $is_author ) {
    $archive_title = get_the_author_meta( 'display_name', $queried->data->ID ?? 0 );
    $archive_desc  = get_the_author_meta( 'description', $queried->data->ID ?? 0 );
    $badge_label   = 'Autor';
} elseif ( $is_date ) {
    $archive_title = get_the_date( 'F Y' );
    $archive_desc  = '';
    $badge_label   = 'Archivo';
} else {
    $archive_title = get_the_archive_title();
    $archive_desc  = get_the_archive_description();
    $badge_label   = 'Archivo';
}
?>

<div id="cozy-blog">

<!-- ============================================================ -->
<!--  ARCHIVE HEADER                                               -->
<!-- ============================================================ -->
<div class="bg-gradient-to-b from-cozy-cream to-cozy-sand/60 py-14 md:py-20 px-6 md:px-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto text-center">

        <div class="inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-4">
            🌿 <?php echo esc_html( $badge_label ); ?>
        </div>

        <?php if ( $is_author ) :
            $author_id = $queried->data->ID ?? 0;
        ?>
        <div class="flex justify-center mb-4">
            <?php echo get_avatar( $author_id, 72, '', '', [ 'class' => 'rounded-full border-4 border-white shadow-md' ] ); ?>
        </div>
        <?php endif; ?>

        <h1 class="font-serif text-4xl md:text-5xl font-bold text-cozy-coffee">
            <?php echo esc_html( $archive_title ); ?>
        </h1>

        <?php if ( $archive_desc ) : ?>
        <p class="text-sm md:text-base text-cozy-coffee/70 mt-4 max-w-xl mx-auto font-light leading-relaxed">
            <?php echo wp_kses_post( $archive_desc ); ?>
        </p>
        <?php endif; ?>

        <p class="text-xs text-cozy-coffee/40 mt-3">
            <?php
            $count = $GLOBALS['wp_query']->found_posts;
            echo esc_html( sprintf(
                $count === 1 ? '%d artículo' : '%d artículos',
                $count
            ) );
            ?>
        </p>

    </div>
</div>

<!-- ============================================================ -->
<!--  POSTS GRID                                                   -->
<!-- ============================================================ -->
<div class="py-16 md:py-20 px-6 md:px-12 max-w-7xl mx-auto">

<?php if ( have_posts() ) : ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php while ( have_posts() ) : the_post();
        $cats     = get_the_category();
        $cat      = $cats ? $cats[0] : null;
        $thumb    = get_the_post_thumbnail_url( null, 'medium_large' );
    ?>
        <article <?php post_class( 'group bg-white rounded-[28px] border border-cozy-sand shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col' ); ?>>

            <!-- Featured image -->
            <a href="<?php the_permalink(); ?>" class="block no-underline shrink-0" tabindex="-1" aria-hidden="true">
                <div class="h-52 bg-cozy-cream overflow-hidden flex items-center justify-center">
                    <?php if ( $thumb ) : ?>
                    <img src="<?php echo esc_url( $thumb ); ?>"
                         alt="<?php the_title_attribute(); ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                         loading="lazy">
                    <?php else : ?>
                    <svg class="text-cozy-coffee/20" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Content -->
            <div class="p-6 flex flex-col flex-grow gap-3">

                <div class="flex items-center gap-3 flex-wrap">
                    <?php if ( $cat ) : ?>
                    <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
                       class="inline-flex items-center bg-cozy-mintLight text-cozy-mint text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-cozy-mint/20 hover:bg-cozy-mint hover:text-white transition-colors no-underline">
                        <?php echo esc_html( $cat->name ); ?>
                    </a>
                    <?php endif; ?>
                    <span class="text-[11px] text-cozy-coffee/50">
                        <?php echo esc_html( get_the_date( 'j M Y' ) ); ?>
                    </span>
                </div>

                <h2 class="font-serif text-xl font-bold text-cozy-coffee leading-snug m-0">
                    <a href="<?php the_permalink(); ?>"
                       class="hover:text-cozy-mint transition-colors no-underline">
                        <?php the_title(); ?>
                    </a>
                </h2>

                <p class="text-sm text-cozy-coffee/70 leading-relaxed line-clamp-3 m-0 flex-grow">
                    <?php echo wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ), 25 ); ?>
                </p>

                <a href="<?php the_permalink(); ?>"
                   class="inline-flex items-center gap-2 text-xs font-bold text-cozy-mint hover:gap-3 transition-all no-underline mt-auto pt-3 border-t border-cozy-sand">
                    Leer artículo
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>

            </div>
        </article>
    <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ( $GLOBALS['wp_query']->max_num_pages > 1 ) : ?>
    <div class="mt-14 flex justify-center">
        <?php
        echo paginate_links( [
            'prev_text' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg> Anterior',
            'next_text' => 'Siguiente <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',
            'type'      => 'list',
        ] );
        ?>
    </div>
    <?php endif; ?>

<?php else : ?>

    <div class="text-center py-24">
        <svg class="mx-auto text-cozy-coffee/20 block mb-6" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        <p class="text-cozy-coffee/60 font-medium">No hay artículos en esta sección todavía.</p>
        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"
           class="inline-block mt-6 bg-cozy-mint text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-cozy-mintDark transition-colors no-underline">
            Ver todos los artículos
        </a>
    </div>

<?php endif; ?>

</div><!-- /.max-w-7xl -->
</div><!-- /#cozy-blog -->

<?php get_footer(); ?>
