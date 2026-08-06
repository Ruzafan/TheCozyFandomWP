# Modo "Extra Cozy" — Diseño

Fecha: 2026-08-06

## Objetivo

Añadir un toggle opcional en el header que active una capa extra de ambientación
"cozy" en desktop, sin saturar la experiencia por defecto. Desactivado por defecto.

## Alcance

- Solo aplica en desktop (`min-width: 1024px`). En mobile el toggle puede existir
  pero no despliega los efectos visuales (sin espacio/cursor real).
- Persistencia vía `localStorage` (`cozyMode: "on"/"off"`), aplicada antes del
  primer paint para evitar parpadeo.
- Assets placeholder (SVG simples, estilo cozy) — sustituibles después por diseño final.

## Arquitectura

- Toggle en `header.php`, junto a los controles existentes (icono 🌿).
- Click → añade/quita `class="extra-cozy"` en `<body>` + guarda preferencia.
- Script inline mínimo en `<head>` (antes del render) que lee localStorage y aplica
  la clase de inmediato, evitando flash de contenido sin estilo cozy.
- Lógica principal en `assets/js/cozy-mode.js`, encolado con `wp_enqueue_script`.
- Estilos en un bloque dedicado dentro de `src/input.css` (o archivo separado
  compilado por Tailwind), todo scoped bajo `.extra-cozy`.

## Componentes

### 1. Toggle "Cozy it"
- Botón en header con `aria-pressed`, label accesible "Activar modo Extra Cozy".
- Estado apagado: icono outline discreto. Estado encendido: icono relleno + glow cálido sutil.
- Micro-animación de confirmación al activar (breve puff/brillo).

### 2. Plantas colgantes en el header
- 2–3 SVGs de plantas colgantes, `position: absolute` en huecos laterales vacíos del header.
- `pointer-events: none` para no bloquear clics.
- Animación de balanceo suave (`@keyframes sway`, pocos grados, 4–6s ease-in-out infinite).
- Solo visibles con `.extra-cozy` + desktop.

### 3. Cursor cozy
- `cursor: url('cozy-cursor.svg') 4 4, auto;` en `body.extra-cozy`.
- SVG placeholder tipo hoja/taza, 24×24, con fallback a `auto`.

### 4. Gatito sticky con interacción
- Elemento `position: fixed; bottom: 0;` en esquina (inferior derecha), z-index alto
  pero por debajo de modales.
- Animación idle en CSS (parpadeo, respiración/cola).
- Interacción: `mouseenter` cercano o click → clase temporal `.cat-happy` (~1.5s,
  salto/ronroneo/corazón flotante) y vuelta a idle.
- Botón pequeño de cerrar (X) para ocultarlo temporalmente sin desactivar todo el modo;
  reaparece en la próxima carga si el modo sigue activo.

## Fuera de alcance (esta iteración)
- Soporte mobile con efectos completos.
- Ilustraciones finales (se usan placeholders SVG).
- Persistencia de "gatito oculto" entre sesiones (se resetea en próxima carga).

## Testing
- Verificar toggle enciende/apaga sin recargar la página.
- Verificar persistencia tras recargar y navegar entre páginas.
- Verificar que en mobile no se muestran plantas/gatito/cursor custom.
- Verificar que las plantas y el gatito no bloquean clics en contenido real
  (`pointer-events` correcto).
- Revisar accesibilidad del botón toggle (foco, `aria-pressed`, contraste).
