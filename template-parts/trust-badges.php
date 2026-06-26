<?php
/**
 * Trust Badges Template Part
 *
 * @package CozyFandom
 */

defined( 'ABSPATH' ) || exit;

$icon_bg_class   = $args['icon_bg_class'] ?? 'bg-white shadow-sm';
$text_size_title = $args['text_size_title'] ?? 'text-[13px]';
$text_size_desc  = $args['text_size_desc'] ?? 'text-[11px]';
$text_desc_muted = $args['text_desc_muted'] ?? 'text-cozy-coffee/60';
$item_class      = $args['item_class'] ?? 'py-4 md:py-5';

$badges = [
	[
		'title' => 'Empaquetado Aesthetic',
		'desc'  => 'Unboxings que enamoran',
		'svg'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
	],
	[
		'title' => 'Envíos Rápidos',
		'desc'  => '24/48h en Península',
		'svg'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
	],
	[
		'title' => 'Pagos 100% Seguros',
		'desc'  => 'Tarjeta, Google Pay, Apple Pay',
		'svg'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
	],
	[
		'title' => 'Atención Cercana',
		'desc'  => 'Te ayudamos por WhatsApp',
		'svg'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#88C4B5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
	],
];

$count = count( $badges );
$i     = 0;
foreach ( $badges as $badge ) :
	$i++;
	$style = '';
	$class = $item_class;
	if ( ! empty( $args['has_border'] ) && $i < $count ) {
		$style = ' style="border-bottom: 1px solid rgba(180,160,140,0.15);"';
	}
	?>
	<div class="flex items-center gap-3 <?php echo esc_attr( $class ); ?>"<?php echo $style; // phpcs:ignore ?>>
		<div class="shrink-0 w-10 h-10 rounded-[12px] flex items-center justify-center <?php echo esc_attr( $icon_bg_class ); ?>">
			<?php echo $badge['svg']; // phpcs:ignore ?>
		</div>
		<div>
			<p class="font-bold text-cozy-coffee m-0 leading-tight <?php echo esc_attr( $text_size_title ); ?>"><?php echo esc_html( $badge['title'] ); ?></p>
			<p class="m-0 leading-tight <?php echo esc_attr( $text_desc_muted ); ?> <?php echo esc_attr( $text_size_desc ); ?>"><?php echo esc_html( $badge['desc'] ); ?></p>
		</div>
	</div>
<?php endforeach; ?>
