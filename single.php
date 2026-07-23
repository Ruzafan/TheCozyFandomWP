<?php
/**
 * Single Blog Post – Cozy Fandom Design
 *
 * @package cozy-fandom-child
 */

get_header();
the_post();

$cats      = get_the_category();
$cat       = $cats ? $cats[0] : null;
$thumb_url = get_the_post_thumbnail_url( null, 'full' );
$author_id = get_the_author_meta( 'ID' );
$read_time = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) );
?>

<div id="cozy-single-post">

<!-- ============================================================ -->
<!--  POST HEADER                                                  -->
<!-- ============================================================ -->
<div class="relative bg-cozy-cream overflow-hidden">

    <?php if ( $thumb_url ) : ?>
    <!-- Featured image with gradient overlay -->
    <div class="relative h-64 md:h-[480px] w-full">
        <img src="<?php echo esc_url( $thumb_url ); ?>"
             alt="<?php the_title_attribute(); ?>"
             class="absolute inset-0 w-full h-full object-cover object-center"
             loading="eager">
        <div class="absolute inset-0"
             style="background:linear-gradient(180deg, rgba(252,249,245,0) 30%, rgba(252,249,245,0.7) 70%, #FCF9F5 100%);"></div>
    </div>
    <?php else : ?>
    <!-- No image: decorative gradient header -->
    <div class="h-32 md:h-48 bg-gradient-to-b from-cozy-sand/60 to-cozy-cream"></div>
    <?php endif; ?>

    <!-- Title card -->
    <div class="relative z-10 max-w-3xl mx-auto px-6 md:px-8 <?php echo $thumb_url ? '-mt-24 md:-mt-40' : 'pt-10'; ?> pb-10">

        <!-- Category + meta -->
        <div class="flex items-center flex-wrap gap-3 mb-4">
            <?php if ( $cat ) : ?>
            <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
               class="inline-flex items-center bg-cozy-mintLight text-cozy-mint text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-cozy-mint/20 hover:bg-cozy-mint hover:text-white transition-colors no-underline">
                <?php echo esc_html( $cat->name ); ?>
            </a>
            <?php endif; ?>
            <span class="text-xs text-cozy-coffee/50">
                <?php echo esc_html( get_the_date( 'j M Y' ) ); ?>
            </span>
            <span class="text-xs text-cozy-coffee/40">·</span>
            <span class="text-xs text-cozy-coffee/50"><?php echo absint( $read_time ); ?> min de lectura</span>
        </div>

        <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-cozy-coffee leading-tight m-0">
            <?php the_title(); ?>
        </h1>

        <?php if ( has_excerpt() ) : ?>
        <p class="text-base text-cozy-coffee/70 mt-4 leading-relaxed max-w-2xl">
            <?php the_excerpt(); ?>
        </p>
        <?php endif; ?>

        <!-- Author row -->
        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-cozy-sand">
            <?php echo get_avatar( $author_id, 40, '', '', [ 'class' => 'rounded-full border-2 border-cozy-sand flex-shrink-0' ] ); ?>
            <div>
                <span class="text-sm font-bold text-cozy-coffee block leading-tight">
                    <?php the_author(); ?>
                </span>
                <span class="text-xs text-cozy-coffee/50">
                    <?php echo esc_html( get_the_date() ); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!--  POST CONTENT                                                 -->
<!-- ============================================================ -->
<div class="max-w-3xl mx-auto px-6 md:px-8 pb-16">
    <div class="cozy-post-content">
        <?php the_content(); ?>
    </div>

    <!-- Tags -->
    <?php $tags = get_the_tags(); if ( $tags ) : ?>
    <div class="flex flex-wrap gap-2 mt-10 pt-8 border-t border-cozy-sand">
        <span class="text-xs text-cozy-coffee/50 font-bold uppercase tracking-wider self-center mr-1">Etiquetas:</span>
        <?php foreach ( $tags as $tag ) : ?>
        <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"
           class="inline-flex items-center bg-cozy-cream hover:bg-cozy-mintLight text-cozy-coffee/70 hover:text-cozy-mint text-[11px] font-bold px-3 py-1 rounded-full border border-cozy-sand hover:border-cozy-mint/30 transition-colors no-underline">
            <?php echo esc_html( $tag->name ); ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Prev / Next navigation -->
    <nav class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-12" aria-label="Navegación entre artículos">
        <?php
        $prev = get_previous_post();
        $next = get_next_post();
        ?>

        <?php if ( $prev ) : ?>
        <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>"
           class="group flex flex-col gap-1.5 bg-white border border-cozy-sand rounded-[20px] p-5 hover:border-cozy-mint hover:shadow-md transition-all no-underline">
            <span class="text-[10px] font-bold uppercase tracking-wider text-cozy-coffee/40 flex items-center gap-1">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Anterior
            </span>
            <span class="text-sm font-bold text-cozy-coffee group-hover:text-cozy-mint transition-colors leading-snug line-clamp-2">
                <?php echo esc_html( get_the_title( $prev ) ); ?>
            </span>
        </a>
        <?php else : ?>
        <div></div>
        <?php endif; ?>

        <?php if ( $next ) : ?>
        <a href="<?php echo esc_url( get_permalink( $next ) ); ?>"
           class="group flex flex-col gap-1.5 bg-white border border-cozy-sand rounded-[20px] p-5 hover:border-cozy-mint hover:shadow-md transition-all no-underline sm:text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-cozy-coffee/40 flex items-center gap-1 sm:justify-end">
                Siguiente
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </span>
            <span class="text-sm font-bold text-cozy-coffee group-hover:text-cozy-mint transition-colors leading-snug line-clamp-2">
                <?php echo esc_html( get_the_title( $next ) ); ?>
            </span>
        </a>
        <?php endif; ?>
    </nav>
</div>

<!-- ============================================================ -->
<!--  RELATED POSTS                                                -->
<!-- ============================================================ -->
<?php
$related = get_posts( [
    'category__in'   => wp_get_post_categories( get_the_ID() ),
    'post__not_in'   => [ get_the_ID() ],
    'posts_per_page' => 3,
    'orderby'        => 'rand',
] );
if ( $related ) :
?>
<div class="bg-cozy-sand/40 py-14 px-6 md:px-12">
    <div class="max-w-7xl mx-auto">

        <div class="flex items-center gap-3 mb-8">
            <span class="text-xs font-bold uppercase tracking-wider text-cozy-mint">También te puede gustar</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ( $related as $rel ) :
            $rel_cats  = get_the_category( $rel->ID );
            $rel_cat   = $rel_cats ? $rel_cats[0] : null;
            $rel_thumb = get_the_post_thumbnail_url( $rel->ID, 'medium_large' );
        ?>
            <article class="group bg-white rounded-[24px] border border-cozy-sand shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">

                <a href="<?php echo esc_url( get_permalink( $rel ) ); ?>" class="block shrink-0" tabindex="-1" aria-hidden="true">
                    <div class="h-44 bg-cozy-cream overflow-hidden flex items-center justify-center">
                        <?php if ( $rel_thumb ) : ?>
                        <img src="<?php echo esc_url( $rel_thumb ); ?>"
                             alt="<?php echo esc_attr( get_the_title( $rel ) ); ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy">
                        <?php else : ?>
                        <svg class="text-cozy-coffee/20" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <?php endif; ?>
                    </div>
                </a>

                <div class="p-5 flex flex-col flex-grow gap-2">
                    <?php if ( $rel_cat ) : ?>
                    <span class="inline-flex items-center bg-cozy-mintLight text-cozy-mint text-[10px] font-bold px-2.5 py-0.5 rounded-full self-start uppercase tracking-wider">
                        <?php echo esc_html( $rel_cat->name ); ?>
                    </span>
                    <?php endif; ?>

                    <h3 class="font-serif text-base font-bold text-cozy-coffee leading-snug m-0">
                        <a href="<?php echo esc_url( get_permalink( $rel ) ); ?>"
                           class="hover:text-cozy-mint transition-colors no-underline">
                            <?php echo esc_html( get_the_title( $rel ) ); ?>
                        </a>
                    </h3>

                    <a href="<?php echo esc_url( get_permalink( $rel ) ); ?>"
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-cozy-mint no-underline mt-auto pt-3 border-t border-cozy-sand/60">
                        Leer artículo
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"
               class="inline-flex items-center gap-2 text-sm font-bold text-cozy-coffee hover:text-cozy-mint transition-colors no-underline border border-cozy-sand hover:border-cozy-mint bg-white px-6 py-3 rounded-full hover:bg-cozy-mintLight">
                Ver todos los artículos
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

</div><!-- /#cozy-single-post -->

<?php get_footer(); ?>
