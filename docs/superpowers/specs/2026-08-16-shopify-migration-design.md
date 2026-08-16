# Migración WordPress/WooCommerce → Shopify — Diseño

**Fecha**: 2026-08-16
**Alcance**: Recrear la capa de tema (diseño + funcionalidades interactivas custom) de The Cozy Fandom como un tema Shopify Online Store 2.0, en la carpeta `tcf-shopify/`. Productos, colecciones y catálogo quedan **fuera de alcance** — el usuario los migra manualmente vía export/import de Shopify.

## Punto de partida

- Base: tema **Dawn** (oficial de Shopify), clonado sin historial git en `tcf-shopify/`.
- Fuente de verdad del diseño/funcionalidad actual: tema hijo WP en la raíz del repo (`header.php`, `front-page.php`, `functions.php`, `assets/js/cozy-main.js`, `src/input.css`, `theme.json`, `tailwind.config.js`, `woocommerce/*`, `template-parts/*`).
- La carpeta `the-cozy-fandom-shopify/` y `the-cozy-fandom-shopify.zip` existentes (de una sesión previa) se ignoran y no se reutilizan.

## Identidad visual

Trasladada literalmente desde `theme.json` / `tailwind.config.js`:

| Token | Valor |
|---|---|
| cozy-cream | `#FAF6EE` / `#FCF9F5` |
| cozy-sand | `#F2E6D5` |
| cozy-mint | `#88C4B5` (dark `#72b0a2`, light `#EAF6F3`) |
| cozy-coffee | `#3A3128` / `#4A3F35` |
| cozy-accent | `#D4A373` |
| Tipografía títulos | Playfair Display |
| Tipografía cuerpo | Plus Jakarta Sans |
| Radio de borde | 16px |

Se definen como CSS custom properties en `assets/cozy-theme.css`, cargado por `layout/theme.liquid`, y opcionalmente también expuestos como ajustes de tema en `config/settings_schema.json` para que el usuario pueda tocar colores desde el editor de Shopify.

## CSS: sin pipeline Tailwind

`src/input.css` (3261 líneas) es en su mayoría utilidades Tailwind repetidas. En vez de montar un build step (Vite/PostCSS) dentro del tema Shopify, se escribe **CSS plano** a mano en `assets/cozy-theme.css` cubriendo solo las clases/reglas realmente usadas por los componentes portados. No se arrastra Tailwind como dependencia del tema Shopify.

## Componentes / secciones a portar (todos)

Cada uno como *section* o *snippet* Liquid, montado sobre la estructura de Dawn:

1. **Header** — announcement bar + logo + búsqueda + iconos + nav de categorías → `sections/header.liquid` (extendiendo el de Dawn), usa el **predictive search nativo de Dawn** en vez del buscador custom por WooCommerce.
2. **Footer** — `sections/footer.liquid` (extendiendo el de Dawn).
3. **Hero banner** — `sections/hero-banner.liquid`.
4. **Colecciones destacadas / Top ventas** — `sections/featured-collections.liquid`, `sections/featured-products.liquid` (usan `collection` object de Shopify).
5. **Modo Ultra-Cozy** (clima animado: lluvia/otoño/nieve/sakura, canvas + Web Audio API, rastro de chispas, toasts) — snippet `snippets/cozy-ambient.liquid` + `assets/cozy-main.js`. Es JS puro sin dependencias de WP/WooCommerce: se porta casi 1:1.
6. **Compañero de bebida flotante** — snippet + JS, estado en `localStorage` (igual que WP).
7. **Notas de regalo Kraft en el carrito** — reimplementado con **cart attributes** de Shopify (`cart.attributes['Gift Note']`) vía AJAX Cart API, en vez de sesión PHP.
8. **Oráculo de té** (frase cozy del día) — snippet + JS, contenido estático, sin dependencias de backend.
9. **Badges craft en ficha de producto** (Sello "Selección Cozy", insignias "Tardes Cozy" / "Edición Especial" / "100% Oficial") — integrado en `sections/main-product.liquid`.
10. **Wishlist/favoritos** — Dawn no trae wishlist nativa. Se reimplementa 100% en `localStorage` (igual que el fallback "guest wishlist" que ya usa el WP), sin llamadas a backend.
11. **Cookie banner / consentimiento GTM** — snippet `snippets/cookie-banner.liquid`, mismo patrón de cookie + carga condicional de GTM.

## Explícitamente fuera de alcance / no se porta 1:1

- Checkout, gestión de carrito server-side, hooks de WooCommerce → Shopify ya lo resuelve nativo; solo se adapta el HTML/CSS alrededor.
- Productos, colecciones, pedidos, clientes → migración manual por el usuario vía export/import de Shopify.
- Cualquier lógica PHP de WooCommerce sin equivalente directo en Liquid se reemplaza por el objeto/API nativo de Shopify más cercano (cart AJAX API, predictive search, `localStorage`), nunca se intenta emular WooCommerce dentro de Shopify.

## Entrega

- Carpeta `tcf-shopify/` con estructura Shopify válida (`layout/`, `sections/`, `snippets/`, `templates/`, `assets/`, `config/`, `locales/`) lista para subir desde **Online Store → Themes → Add theme → Upload zip**.
- No se genera zip automáticamente durante el desarrollo (se genera al final, una vez validado).
- Instrucciones de instalación en Shopify se entregan al usuario al final.

## Testing / validación

- Validación manual: cada sección/snippet se revisa por sintaxis Liquid válida (no hay theme-check/CLI de Shopify instalado localmente salvo que se añada).
- Si Shopify CLI está disponible, se recomienda `shopify theme check` sobre `tcf-shopify/` antes de la entrega final.
- Verificación funcional real (subir a una tienda de desarrollo) queda a cargo del usuario, dado que no hay acceso a una tienda Shopify desde este entorno.
