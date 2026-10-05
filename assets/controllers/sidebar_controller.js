// @ts-check

import { Controller } from '@hotwired/stimulus';

/**
 * @typedef SidebarControllerContext
 *
 * @property {HTMLElement} headerTarget
 * @property {HTMLButtonElement} toggleTarget
 * @property {HTMLElement} sidebarTarget
 * @property {HTMLElement} mainTarget
 *
 * @property {boolean} openValue
 * @property {string} openLabelValue
 * @property {string} closeLabelValue
 *
 * @property {MediaQueryList} desktopQuery
 * @property {(event: MediaQueryListEvent) => void} onViewportChange
 *
 * @property {function(): void} initialize
 * @property {function(): void} connect
 * @property {function(): void} disconnect
 * @property {function(boolean, boolean=): void} openValueChanged
 * @property {function(): void} toggle
 * @property {function(): void} open
 * @property {function(): void} close
 * @property {function(KeyboardEvent): void} closeOnEscape
 * @property {function(): boolean} isDesktop
 * @property {function(): void} syncAccessibility
 */

/** Matches Tailwind `xl` (80rem). */
const DESKTOP_MEDIA = '(min-width: 1280px)';

const SIDEBAR_TITLE_ID = 'app-sidebar-title';

/**
 * App sidebar toggle (body controller in two_column.html.twig).
 *
 * Below `xl` the sidebar is a modal drawer: `inert` when closed, `inert` on
 * header/main when open. From `xl` it is static navigation (never inert).
 *
 * Targets:
 * - header: app header (inert while the drawer is open)
 * - toggle: hamburger button (hidden from `xl`)
 * - sidebar: aside navigation panel
 * - main: primary content (inert while the drawer is open)
 * - overlay: backdrop on narrow viewports (click closes; not a Stimulus target)
 *
 * Values:
 * - open: whether the mobile drawer is visible
 * - openLabel / closeLabel: aria-label text for the toggle button
 *
 * Actions:
 * - toggle / open / close: control drawer visibility
 * - closeOnEscape: close the drawer on Escape (narrow viewports only)
 *
 * @extends {Controller}
 */
export default class extends Controller {
    static targets = ['header', 'toggle', 'sidebar', 'main'];

    static values = {
        open: { type: Boolean, default: false },
        openLabel: String,
        closeLabel: String,
    };

    /**
     * @this {SidebarControllerContext}
     */
    initialize() {
        this.desktopQuery = window.matchMedia(DESKTOP_MEDIA);
    }

    /**
     * @this {SidebarControllerContext}
     */
    connect() {
        this.onViewportChange = () => {
            if (this.isDesktop()) {
                this.openValue = false;
            }

            this.syncAccessibility();
        };

        this.desktopQuery.addEventListener('change', this.onViewportChange);

        this.syncAccessibility();
    }

    /**
     * @this {SidebarControllerContext}
     */
    disconnect() {
        this.desktopQuery.removeEventListener('change', this.onViewportChange);
    }

    /**
     * @this {SidebarControllerContext}
     *
     * @param {boolean} isOpen
     * @param {boolean} [wasOpen]
     */
    openValueChanged(isOpen, wasOpen) {
        this.syncAccessibility();

        if (wasOpen === undefined || this.isDesktop()) {
            return;
        }

        if (isOpen) {
            this.sidebarTarget.focus();

            return;
        }

        if (wasOpen) {
            this.toggleTarget.focus();
        }
    }

    /**
     * @this {SidebarControllerContext}
     */
    toggle() {
        this.openValue = !this.openValue;
    }

    /**
     * @this {SidebarControllerContext}
     */
    open() {
        this.openValue = true;
    }

    /**
     * @this {SidebarControllerContext}
     */
    close() {
        this.openValue = false;
    }

    /**
     * @this {SidebarControllerContext}
     *
     * @param {KeyboardEvent} event
     */
    closeOnEscape(event) {
        if (this.isDesktop() || !this.openValue) {
            return;
        }

        event.preventDefault();

        this.close();
    }

    /**
     * @this {SidebarControllerContext}
     *
     * @returns {boolean}
     */
    isDesktop() {
        return this.desktopQuery.matches;
    }

    /**
     * @this {SidebarControllerContext}
     */
    syncAccessibility() {
        const isDesktop = this.isDesktop();
        const isDrawerOpen = !isDesktop && this.openValue;

        this.headerTarget.inert = isDrawerOpen;
        this.mainTarget.inert = isDrawerOpen;
        this.sidebarTarget.inert = !isDesktop && !this.openValue;

        this.sidebarTarget.removeAttribute('aria-hidden');

        if (isDrawerOpen) {
            this.sidebarTarget.setAttribute('role', 'dialog');
            this.sidebarTarget.setAttribute('aria-modal', 'true');
            this.sidebarTarget.setAttribute('aria-labelledby', SIDEBAR_TITLE_ID);
        } else {
            this.sidebarTarget.removeAttribute('role');
            this.sidebarTarget.removeAttribute('aria-modal');
            this.sidebarTarget.removeAttribute('aria-labelledby');
        }

        this.toggleTarget.setAttribute('aria-expanded', String(isDrawerOpen));
        this.toggleTarget.setAttribute(
            'aria-label',
            isDrawerOpen ? this.closeLabelValue : this.openLabelValue,
        );
    }
}
