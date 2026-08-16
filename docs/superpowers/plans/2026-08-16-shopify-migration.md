# Migración Shopify (tcf-shopify) — Plan de Implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir `tcf-shopify/`, un tema Shopify Online Store 2.0 basado en Dawn, con la identidad visual y las funcionalidades interactivas custom de The Cozy Fandom.

**Architecture:** Dawn como esqueleto (layout/objects nativos de Shopify: cart AJAX, predictive search, secciones OS 2.0). Encima, un único snippet de estilos (`assets/cozy-theme.css`), un único JS de comportamiento (`assets/cozy-main.js`), y snippets/sections nuevos para cada feature custom, todos cargados desde `layout/theme.liquid`.

**Tech Stack:** Liquid, Tailwind CSS vía CDN script (`cdn.tailwindcss.com`, sin build step), JS vanilla (IIFE, sin dependencias), Shopify Cart AJAX API (`/cart.js`, `/cart/add.js`, `/cart/change.js`, `/cart/update.js`), `localStorage` para wishlist/preferencias.

## Global Constraints

- No se usa la carpeta/zip `the-cozy-fandom-shopify/` como base ni se copia tal cual; se usa solo como referencia de contenido ya pensado.
- No se monta pipeline de build (Vite/PostCSS) — Tailwind se carga vía CDN script tag en `theme.liquid`.
- Paleta: cream `#FAF6EE`, sand `#F2E6D5`, mint `#88C4B5` (dark `#72b0a2`, light `#EAF6F3`), coffee `#3A3128`, accent `#D4A373`. Tipografía: Playfair Display (serif/títulos), Plus Jakarta Sans (sans/cuerpo). Radio 16px.
- Wishlist y nota de regalo NO llaman a WooCommerce ni a ningún backend propio: wishlist en `localStorage`, nota de regalo en `cart.attributes['Nota de regalo']` vía `/cart/update.js`.
- Productos/colecciones/checkout: fuera de alcance, no se tocan.

---

### Task 1: Configurar Tailwind CDN + paleta + tipografía en el layout

**Files:**
- Modify: `tcf-shopify/layout/theme.liquid`

- [ ] Añadir en `<head>`, antes del cierre de `</head>`, el script CDN de Tailwind con config inline:

```html
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          cozy: {
            cream: '#FAF6EE',
            sand: '#F2E6D5',
            coffee: '#3A3128',
            mint: '#88C4B5',
            mintDark: '#72b0a2',
            mintLight: '#EAF6F3',
            accent: '#D4A373',
          }
        },
        fontFamily: {
          sans: ['"Plus Jakarta Sans"', 'sans-serif'],
          serif: ['"Playfair Display"', 'serif'],
        },
        borderRadius: { '4xl': '32px', '3xl': '24px' }
      }
    }
  }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ 'cozy-theme.css' | asset_url }}">
```

- [ ] Antes del cierre de `</body>`, añadir:

```html
<script src="{{ 'cozy-main.js' | asset_url }}" defer></script>
{% render 'cookie-banner' %}
{% render 'cozy-ambient' %}
{% render 'drink-companion' %}
{% render 'tea-oracle-modal' %}
```

- [ ] Verificar: abrir `tcf-shopify/layout/theme.liquid` y confirmar que las etiquetas `{% ... %}` están bien cerradas (sin `{%-` sin `-%}` correspondiente) y que el archivo sigue siendo Liquid válido (revisión visual, no hay linter local).

- [ ] Commit:
```bash
git add tcf-shopify/layout/theme.liquid
git commit -m "feat(shopify): wire up Tailwind CDN, cozy palette and global includes in theme.liquid"
```

---

### Task 2: Crear `assets/cozy-theme.css` con estilos que Tailwind no cubre

**Files:**
- Create: `tcf-shopify/assets/cozy-theme.css`

**Interfaces:**
- Produces: clases `.cozy-announcement-bar`, `.cozy-hdr-*`, `.cozy-fav-btn.is-favorited .cozy-fav-heart`, `#cozy-toast`, `.line-clamp-2` (fallback), animaciones de partículas ya cubiertas por canvas (no CSS).

- [ ] Escribir el archivo con: fondo body en `--cozy-cream`, fuente body `font-family: "Plus Jakarta Sans"`, títulos `font-family: "Playfair Display"`, estilos del announcement bar (gradiente sand→mintLight), estilos base de scrollbar, `.cozy-fav-btn.is-favorited svg { fill: #f87171; stroke: #f87171; }`, y `z-index` scale documentado en comentario (997 canvas, 998 widgets flotantes, 1000-1001 drawers, 2000 modales, 3000 cookie banner, 4000 toast) para evitar colisiones al añadir nuevas secciones:

```css
:root {
  --cozy-cream: #FAF6EE;
  --cozy-sand: #F2E6D5;
  --cozy-coffee: #3A3128;
  --cozy-mint: #88C4B5;
  --cozy-mint-dark: #72b0a2;
  --cozy-mint-light: #EAF6F3;
  --cozy-accent: #D4A373;
}

body {
  background-color: var(--cozy-cream);
  color: var(--cozy-coffee);
  font-family: "Plus Jakarta Sans", sans-serif;
}

h1, h2, h3, .font-serif {
  font-family: "Playfair Display", serif;
}

.cozy-announcement-bar {
  background: linear-gradient(90deg, var(--cozy-sand) 0%, var(--cozy-mint-light) 100%);
  color: var(--cozy-coffee);
  font-size: 12px;
}

.cozy-fav-btn.is-favorited .cozy-fav-heart {
  fill: #f87171;
  stroke: #f87171;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* z-index scale: 997 ambient canvas, 998 floating widgets,
   1000-1001 drawers, 2000 modals, 3000 cookie banner, 4000 toast */
```

- [ ] Commit:
```bash
git add tcf-shopify/assets/cozy-theme.css
git commit -m "feat(shopify): add cozy-theme.css with palette tokens and non-utility styles"
```

---

### Task 3: Portar `cozy-main.js`

**Files:**
- Create: `tcf-shopify/assets/cozy-main.js`

**Interfaces:**
- Produces: `window.cozyShowToast`, `window.openCart/closeCart`, `window.openFavorites/closeFavorites`, `window.toggleMobileMenu/closeMobileMenu`, `window.toggleUltraCozy`, `window.cozySetWeather`, `window.cozyOpenOracle/cozyRefreshOracle/cozyCloseOracle`, delegación global de `[data-action]` sobre `document`.
- Consumes (en runtime, vía Cart AJAX API): `/cart.js`, `/cart/add.js`, `/cart/change.js`, `/cart/update.js`, `/search/suggest.json`.

- [ ] Copiar el contenido íntegro (877 líneas) de `the-cozy-fandom-shopify/assets/cozy-main.js` a `tcf-shopify/assets/cozy-main.js` sin modificaciones — ya implementa correctamente: toasts, drawers de carrito/favoritos, AJAX cart engine (`refreshCartDrawer`, `quickAddToCart`, `removeCartItem`, `saveGiftNote` vía `cart.attributes['Nota de regalo']`), wishlist en `localStorage` (`STORAGE_WISHLIST = 'cozy_shopify_favorites'`), predictive search contra `/search/suggest.json`, oráculo de té, compañero de bebida, canvas de clima (lluvia/otoño/nieve/sakura) con Web Audio API, consentimiento de cookies, y el dispatcher global de `data-action`.
- [ ] Verificar: `grep -c "^" tcf-shopify/assets/cozy-main.js` debe devolver `877`.
- [ ] Commit:
```bash
git add tcf-shopify/assets/cozy-main.js
git commit -m "feat(shopify): port cozy-main.js interactive engine (cart, wishlist, weather, oracle)"
```

---

### Task 4: Snippets de widgets flotantes (ambient, drink companion, tea oracle, cookie banner)

**Files:**
- Create: `tcf-shopify/snippets/cozy-ambient.liquid`
- Create: `tcf-shopify/snippets/drink-companion.liquid`
- Create: `tcf-shopify/snippets/tea-oracle-modal.liquid`
- Create: `tcf-shopify/snippets/cookie-banner.liquid`

**Interfaces:**
- Consumes: `{% render 'cozy-icons', name: 'close', size: 14 %}` (definido en Task 6).
- Produces: DOM ids consumidos por `cozy-main.js` — `cozy-rain-canvas`, `cozy-weather-selector`, `cozy-drink-widget`, `cozy-drink-modal`, `cozy-oracle-modal`, `cozy-oracle-quote`, `cozy-consent-banner`.

- [ ] Copiar el contenido de los 4 snippets desde `the-cozy-fandom-shopify/snippets/{cozy-ambient,drink-companion,tea-oracle-modal,cookie-banner}.liquid` a las rutas equivalentes en `tcf-shopify/snippets/` sin modificaciones — el markup y los `data-action` ya calzan con el dispatcher de `cozy-main.js`.
- [ ] Verificar: `grep -l "cozy-rain-canvas\|cozy-drink-widget\|cozy-oracle-modal\|cozy-consent-banner" tcf-shopify/snippets/*.liquid` debe listar los 4 archivos.
- [ ] Commit:
```bash
git add tcf-shopify/snippets/cozy-ambient.liquid tcf-shopify/snippets/drink-companion.liquid tcf-shopify/snippets/tea-oracle-modal.liquid tcf-shopify/snippets/cookie-banner.liquid
git commit -m "feat(shopify): add floating widget snippets (weather, drink companion, tea oracle, cookies)"
```

---

### Task 5: Snippets de carrito y favoritos

**Files:**
- Create: `tcf-shopify/snippets/cart-drawer.liquid`
- Create: `tcf-shopify/snippets/favorites-drawer.liquid`

**Interfaces:**
- Consumes: `{% render 'cozy-icons', name: 'close', size: 16 %}`, objeto `cart` de Shopify (`cart.item_count`, `cart.items`, `cart.attributes['Nota de regalo']`, `cart.total_price`).
- Produces: `#cart-drawer`, `#cart-overlay`, `#cart-drawer-items`, `#cart-drawer-total`, `#cozy-gift-checkbox`, `#cozy-gift-field`, `#cozy-gift-text`, `#fav-drawer`, `#fav-overlay`, `#fav-items` — todos consumidos por `cozy-main.js`.

- [ ] Copiar `the-cozy-fandom-shopify/snippets/cart-drawer.liquid` y `favorites-drawer.liquid` a `tcf-shopify/snippets/` sin modificaciones. El carrito ya usa `cart.attributes['Nota de regalo']` como fuente de verdad server-side y se re-sincroniza vía JS tras cada AJAX call.
- [ ] Añadir en `tcf-shopify/layout/theme.liquid` (junto a los renders de Task 1) las líneas:
```html
{% render 'cart-drawer' %}
{% render 'favorites-drawer' %}
```
- [ ] Verificar: abrir el layout y confirmar que ambos renders están presentes una sola vez.
- [ ] Commit:
```bash
git add tcf-shopify/snippets/cart-drawer.liquid tcf-shopify/snippets/favorites-drawer.liquid tcf-shopify/layout/theme.liquid
git commit -m "feat(shopify): add cart drawer (with gift note cart attribute) and favorites drawer"
```

---

### Task 6: Snippet de iconos y product card

**Files:**
- Create: `tcf-shopify/snippets/cozy-icons.liquid`
- Create: `tcf-shopify/snippets/product-card.liquid`

**Interfaces:**
- Produces: `{% render 'product-card', product: product, badge_text: string, rank: number %}` — usado por las secciones de colecciones (Task 8).

- [ ] Copiar `the-cozy-fandom-shopify/snippets/cozy-icons.liquid` (revisar su contenido primero con Read, ya que no se leyó en la exploración previa) y `product-card.liquid` a `tcf-shopify/snippets/` sin modificaciones.
- [ ] Verificar: `{% render 'product-card' %}` renderiza sin variables de producto sin lanzar error (el snippet ya usa `{%- if product != blank -%}` como guard).
- [ ] Commit:
```bash
git add tcf-shopify/snippets/cozy-icons.liquid tcf-shopify/snippets/product-card.liquid
git commit -m "feat(shopify): add icon set snippet and cozy product card"
```

---

### Task 7: Header y footer

**Files:**
- Create: `tcf-shopify/sections/header.liquid` (reemplaza al de Dawn, referenciado desde `layout/theme.liquid`)
- Create: `tcf-shopify/sections/footer.liquid` (reemplaza al de Dawn)

**Interfaces:**
- Consumes: `linklists.main-menu.links` (menú nativo de Shopify) para la navegación de categorías, `cart.item_count` para el badge del carrito, `{% render 'cozy-icons' %}`.
- Produces: `.cozy-hdr-search__input`, `#cozy-search-suggestions`, `#cart-badge`, `#fav-badge`, `#cozy-nav-sidebar`, `#mobile-menu-overlay` — ids que `cozy-main.js` espera.

- [ ] Leer `TheCozyFandomWP/header.php` completo (ya se leyeron las primeras 80 líneas; leer el resto) para extraer la estructura exacta: announcement bar, fila logo+búsqueda+iconos, fila de navegación por categorías, menú móvil lateral.
- [ ] Reescribir esa estructura en Liquid en `sections/header.liquid`, sustituyendo:
  - `home_url('/')` → `routes.root_url`
  - `get_theme_mod('custom_logo')` → `section.settings.logo` (image_picker) con `{{ section.settings.logo | image_url: width: 160 }}`
  - el buscador AJAX custom por WooCommerce → `<input type="search" class="cozy-hdr-search__input" name="q">` dentro de `<form action="{{ routes.search_url }}" method="get" role="search">`, con `<div id="cozy-search-suggestions" class="hidden ...">` al lado (consumido por `initPredictiveSearch` en `cozy-main.js`)
  - el icono de carrito con badge `<span id="cart-badge" class="{% if cart.item_count == 0 %}hidden{% endif %}">{{ cart.item_count }}</span>` y `data-action="open-cart"`
  - el icono de favoritos con `<span id="fav-badge" class="hidden">0</span>` y `data-action="open-favorites"`
  - la navegación de categorías iterando `{% for link in linklists.main-menu.links %}`
  - checkbox de Modo Ultra-Cozy con `id="cozy-ultra-checkbox"` y `data-action="toggle-ultra-cozy"`
  - `{% schema %}` con al menos: `{"name": "Header", "settings": [{"type": "image_picker", "id": "logo", "label": "Logo"}]}`
- [ ] Reescribir `sections/footer.liquid` con los bloques del footer actual (enlaces legales, redes sociales, newsletter) adaptando el formulario de newsletter a `{% form 'customer', id: 'cozy-newsletter-form' %}` con `contact[tags]` = `newsletter` (patrón estándar de Shopify para signup de email vía Customer form).
- [ ] Verificar: en `tcf-shopify/layout/theme.liquid`, confirmar que existe `{% sections 'header-group' %}` o, si Dawn usa secciones fijas, que `{% section 'header' %}` y `{% section 'footer' %}` apuntan a los archivos nuevos (Dawn OS2.0 normalmente usa JSON de `sections/` grupo `header-group.json`/`footer-group.json`; si es así, actualizar esos JSON para referenciar `type: header` / `type: footer` — mismos nombres de archivo, no requiere cambio si se sobrescriben los `.liquid` existentes de Dawn).
- [ ] Commit:
```bash
git add tcf-shopify/sections/header.liquid tcf-shopify/sections/footer.liquid
git commit -m "feat(shopify): rebuild header and footer with cozy branding and search/cart/wishlist widgets"
```

---

### Task 8: Secciones de home (hero, colecciones destacadas, top ventas)

**Files:**
- Create: `tcf-shopify/sections/hero-banner.liquid`
- Create: `tcf-shopify/sections/featured-collections.liquid`
- Create: `tcf-shopify/sections/featured-products.liquid` (usado como "Top Ventas")

**Interfaces:**
- Consumes: `{% render 'product-card', product: product, rank: forloop.index %}`.

- [ ] Leer `TheCozyFandomWP/front-page.php` completo para extraer estructura y copy del hero, la cuadrícula de colecciones destacadas, y la sección "Top Ventas" (badge `🔥 Los Más Buscados`, gradiente sand→mintLight→mint, botón "Ver todos los productos →").
- [ ] Escribir `sections/hero-banner.liquid` con `{% schema %}` exponiendo `image_picker` para el banner, `text` para título/subtítulo, `url` para el CTA — todos con valores por defecto tomados del contenido actual.
- [ ] Escribir `sections/featured-collections.liquid` iterando `{% for block in section.blocks %}` con bloques tipo `collection` (`type: "collection", settings: [{type: "collection", id: "collection"}]`), renderizando cada una con imagen + título + link.
- [ ] Escribir `sections/featured-products.liquid` con setting `collection` (picker) y `product_limit` (range, default 8), iterando `{% for product in section.settings.collection.products limit: section.settings.product_limit %}` y llamando `{% render 'product-card', product: product, rank: forloop.index %}`; aplicar el fondo degradado `bg-gradient-to-br from-cozy-mintLight/80 via-cozy-cream to-cozy-sand/50` (clases Tailwind, ya disponibles vía CDN) y el badge `🔥 Los Más Buscados`.
- [ ] Añadir las 3 secciones a `tcf-shopify/templates/index.json` en el orden hero → featured-collections → featured-products, con `"type"` apuntando a cada handle de sección.
- [ ] Verificar: `templates/index.json` es JSON válido (`python -c "import json; json.load(open('tcf-shopify/templates/index.json'))"` o revisión visual de comas/llaves).
- [ ] Commit:
```bash
git add tcf-shopify/sections/hero-banner.liquid tcf-shopify/sections/featured-collections.liquid tcf-shopify/sections/featured-products.liquid tcf-shopify/templates/index.json
git commit -m "feat(shopify): add hero, featured collections and top-sales sections to homepage"
```

---

### Task 9: Ficha de producto — badges craft

**Files:**
- Modify: `tcf-shopify/sections/main-product.liquid`

- [ ] Leer `TheCozyFandomWP/woocommerce/single-product.php` para extraer el markup exacto del sello "🏷️ Selección Cozy" (badge sobre la imagen principal) y las 3 insignias (`☕ Tardes Cozy`, `🧸 Edición Especial`, `🌿 100% Oficial`) en el resumen del producto.
- [ ] En la sección de galería de medios de `main-product.liquid` (bloque `media`), añadir el badge `🏷️ Selección Cozy` posicionado en absoluto sobre la primera imagen, visible solo cuando `section.settings.show_cozy_badge` (checkbox nuevo en el `{% schema %}`, default `true`).
- [ ] En el bloque de resumen (`product-title`/`price`), añadir un bloque nuevo de tipo `cozy_badges` en `{% schema %}.blocks` que renderice las 3 insignias como pills, para que el usuario pueda añadirlas/quitarlas desde el editor de temas.
- [ ] Verificar: revisar que el `{% schema %}` sigue siendo JSON válido tras el cambio (mismo método que Task 8).
- [ ] Commit:
```bash
git add tcf-shopify/sections/main-product.liquid
git commit -m "feat(shopify): add cozy seal badge and comfort pills to product page"
```

---

### Task 10: Ajustes de tema (settings_schema) y locales

**Files:**
- Modify: `tcf-shopify/config/settings_schema.json`
- Modify: `tcf-shopify/locales/es.json` (crear si Dawn no trae español por defecto más allá de `en.default.json`)

- [ ] En `settings_schema.json`, añadir un grupo `"name": "Colores Cozy"` con color pickers para `cozy_cream`, `cozy_sand`, `cozy_mint`, `cozy_coffee`, `cozy_accent`, valores por defecto = paleta de la sección Global Constraints. Estos settings son informativos/editables por el usuario; los valores reales activos siguen siendo los del CSS de Task 2 (evita duplicar lógica de theming, pero da control visual básico desde el admin).
- [ ] Confirmar (no crear si ya existe) que `tcf-shopify/locales/es.json` existe con las claves mínimas de UI ya usadas en los snippets copiados (carrito, favoritos, newsletter) — si Dawn no trae `es.json`, copiar `en.default.json` a `locales/es.json` y traducir solo las claves que los snippets/sections nuevos añaden (`cozy_ambient`, `drink_companion`, etc., si se definieron con `{{ 'x' | t }}`; si los snippets copiados usan texto literal en español, este paso puede omitirse — confirmarlo revisando si Task 4-9 usaron `| t` en algún punto; si no se usó, marcar este sub-paso como no aplicable y pasar al commit).
- [ ] Commit:
```bash
git add tcf-shopify/config/settings_schema.json tcf-shopify/locales/es.json
git commit -m "feat(shopify): expose cozy palette in theme settings and confirm es locale"
```

---

### Task 11: Copiar assets de imagen y limpieza final

**Files:**
- Create: `tcf-shopify/assets/logo.webp`, `banner.webp`, `disney.webp`, `harry-potter.webp`, `pokemon.webp`, `pusheen.webp`, `snoopy.webp`, `stitch.webp`, `snoopy-heart.webp` (y sus `.png`/`.jpeg` si algún snippet los referencia directamente)

- [ ] Copiar todas las imágenes desde `the-cozy-fandom-shopify/assets/*.{webp,png,jpeg}` a `tcf-shopify/assets/` (son assets estáticos de marca, no lógica — reutilizables directamente).
- [ ] Revisar `tcf-shopify/sections/*.liquid` y `tcf-shopify/snippets/*.liquid` en busca de referencias a imágenes vía `{{ 'nombre.webp' | asset_url }}` y confirmar que cada nombre referenciado existe en `tcf-shopify/assets/`.
- [ ] Commit:
```bash
git add tcf-shopify/assets/*.webp tcf-shopify/assets/*.png tcf-shopify/assets/*.jpeg
git commit -m "chore(shopify): add brand image assets"
```

---

### Task 12: Validación final y empaquetado

**Files:**
- Read-only pass over all of `tcf-shopify/`

- [ ] Si `shopify` CLI está disponible (`shopify version`), ejecutar `shopify theme check tcf-shopify` y corregir cualquier error de sintaxis Liquid reportado. Si no está disponible, hacer una pasada manual: abrir cada `.liquid` modificado/creado y confirmar que todo `{% if %}`/`{% for %}`/`{% schema %}` tiene su cierre correspondiente.
- [ ] Confirmar que ningún archivo referencia rutas del tema WP (`wp-content`, `home_url`, `get_theme_mod`) — deben ser 0 resultados: `grep -rn "wp-content\|home_url\|get_theme_mod" tcf-shopify/`.
- [ ] Generar el zip final para subir a Shopify, con las carpetas en la raíz del zip (no anidadas):
```bash
cd tcf-shopify && powershell -Command "Compress-Archive -Path * -DestinationPath ../tcf-shopify.zip -Force"
```
- [ ] Commit (sin el zip, que se queda fuera de git vía `.gitignore` si el repo no versiona binarios grandes; si no hay `.gitignore` para zips, añadir `tcf-shopify.zip` a `.gitignore`):
```bash
git add tcf-shopify/ .gitignore
git commit -m "chore(shopify): final validation pass for tcf-shopify theme"
```
- [ ] Entregar al usuario instrucciones de instalación: Shopify Admin → Online Store → Themes → Add theme → Upload zip → seleccionar `tcf-shopify.zip` → Publish (o dejar en borrador para revisar primero).
