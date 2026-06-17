<?php
/**
 * Blog Posts Index – Cozy Fandom Design
 * Used by WordPress as the "posts page" when a static front page is set.
 *
 * @package cozy-fandom-child
 */

get_header();
?>

<div id="cozy-blog">

<!-- ============================================================ -->
<!--  BLOG HEADER                                                  -->
<!-- ============================================================ -->
<div class="bg-gradient-to-b from-cozy-cream to-cozy-sand/60 py-14 md:py-20 px-6 md:px-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-cozy-mint/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 bg-cozy-mintLight text-cozy-mint text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-wider border border-cozy-mint/20 mb-4">
            🌿 Universo Cozy Geek
        </div>
        <h1 class="font-serif text-4xl md:text-5xl font-bold text-cozy-coffee">
            Nuestro Blog
        </h1>
        <p class="text-sm md:text-base text-cozy-coffee/70 mt-4 max-w-xl mx-auto font-light leading-relaxed">
            Artículos, reseñas y curiosidades del mundo friki con alma cozy. Para los que aman los fandoms y el buen gusto.
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
                    <i class="fa-solid fa-pen-nib text-cozy-coffee/20 text-4xl" aria-hidden="true"></i>
                    <?php endif; ?>
                </div>
            </a>

            <!-- Content -->
            <div class="p-6 flex flex-col flex-grow gap-3">

                <!-- Meta row -->
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

                <!-- Title -->
                <h2 class="font-serif text-xl font-bold text-cozy-coffee leading-snug m-0">
                    <a href="<?php the_permalink(); ?>"
                       class="hover:text-cozy-mint transition-colors no-underline">
                        <?php the_title(); ?>
                    </a>
                </h2>

                <!-- Excerpt -->
                <p class="text-sm text-cozy-coffee/70 leading-relaxed line-clamp-3 m-0 flex-grow">
                    <?php echo wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ), 25 ); ?>
                </p>

                <!-- Read more -->
                <a href="<?php the_permalink(); ?>"
                   class="inline-flex items-center gap-2 text-xs font-bold text-cozy-mint hover:gap-3 transition-all no-underline mt-auto pt-3 border-t border-cozy-sand">
                    Leer artículo <i class="fa-solid fa-arrow-right text-[10px]" aria-hidden="true"></i>
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
            'prev_text' => '<i class="fa-solid fa-chevron-left text-xs"></i> Anterior',
            'next_text' => 'Siguiente <i class="fa-solid fa-chevron-right text-xs"></i>',
            'type'      => 'list',
        ] );
        ?>
    </div>
    <?php endif; ?>

<?php else : ?>

    <div class="text-center py-24">
        <i class="fa-solid fa-pen-nib text-cozy-coffee/20 text-6xl block mb-6" aria-hidden="true"></i>
        <p class="text-cozy-coffee/60 font-medium">Aún no hay artículos publicados.</p>
        <?php if ( current_user_can( 'publish_posts' ) ) : ?>
        <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"
           class="inline-block mt-6 bg-cozy-mint text-cozy-coffee px-6 py-3 rounded-xl text-sm font-bold hover:bg-cozy-mintDark transition-colors">
            <i class="fa-solid fa-plus mr-2"></i> Escribir primer artículo
        </a>
        <?php endif; ?>
    </div>

<?php endif; ?>

</div><!-- /.max-w-7xl -->
</div><!-- /#cozy-blog -->

<?php get_footer(); ?>
