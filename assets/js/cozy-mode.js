/**
 * Extra Cozy Mode — opt-in decorative layer (header plants, cozy cursor,
 * sticky cat). Off by default; toggled via the header button and
 * persisted in localStorage. The class itself is applied pre-paint by
 * the inline snippet in header.php — this file only wires interactions.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'cozyMode';
    var body = document.body;

    function isActive() {
        return body.classList.contains( 'extra-cozy' );
    }

    function syncToggleUI( pulse ) {
        var btn = document.getElementById( 'cozy-mode-toggle' );
        if ( ! btn ) return;
        var active = isActive();
        btn.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
        btn.classList.toggle( 'is-active', active );
        if ( pulse ) {
            btn.classList.remove( 'cozy-mode-toggle--pulse' );
            void btn.offsetWidth; /* restart animation */
            btn.classList.add( 'cozy-mode-toggle--pulse' );
        }
    }

    function setCozyMode( active, pulse ) {
        body.classList.toggle( 'extra-cozy', active );
        try {
            localStorage.setItem( STORAGE_KEY, active ? 'on' : 'off' );
        } catch ( e ) {}
        syncToggleUI( pulse );
    }

    var catHappyTimer = null;
    function cozyCatDelight() {
        var cat = document.getElementById( 'cozy-cat' );
        if ( ! cat || cat.classList.contains( 'is-dismissed' ) ) return;
        cat.classList.add( 'is-happy' );
        clearTimeout( catHappyTimer );
        catHappyTimer = setTimeout( function () {
            cat.classList.remove( 'is-happy' );
        }, 1500 );
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        syncToggleUI( false );
    } );

    document.addEventListener( 'click', function ( e ) {
        var toggle = e.target.closest( '[data-action="toggle-cozy-mode"]' );
        if ( toggle ) {
            setCozyMode( ! isActive(), true );
            return;
        }

        var close = e.target.closest( '[data-action="cozy-cat-close"]' );
        if ( close ) {
            var cat = document.getElementById( 'cozy-cat' );
            if ( cat ) cat.classList.add( 'is-dismissed' );
            return;
        }

        if ( e.target.closest( '.cozy-cat__body' ) ) {
            cozyCatDelight();
        }
    } );

    /* Proximity: hovering anywhere over the cat's hit area also delights it. */
    document.addEventListener( 'mouseover', function ( e ) {
        if ( e.target.closest && e.target.closest( '#cozy-cat' ) ) {
            cozyCatDelight();
        }
    } );
})();
