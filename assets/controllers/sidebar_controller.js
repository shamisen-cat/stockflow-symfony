// @ts-check

import { Controller } from '@hotwired/stimulus';

/**
 * @typedef SidebarControllerContext
 *
 * @property {HTMLButtonElement} toggleTarget
 * @property {HTMLElement} sidebarTarget
 * @property {HTMLDivElement} overlayTarget
 *
 * @property {boolean} openValue
 * @property {string} openLabelValue
 * @property {string} closeLabelValue
 *
 * @property {function(): void} connect
 * @property {function(): void} disconnect
 * @property {function(): void} openValueChanged
 * @property {function(): void} toggle
 * @property {function(): void} open
 * @property {function(): void} close
 */

/**
 * @extends {Controller}
 */
export default class extends Controller {
    static targets = ['toggle', 'sidebar', 'overlay'];

    static values = {
        open: { type: Boolean, default: false },
        openLabel: String,
        closeLabel: String,
    };

    /** @this {SidebarControllerContext} */
    openValueChanged() {
        const isOpen = this.openValue;

        this.sidebarTarget.setAttribute('aria-hidden', String(!isOpen));

        this.toggleTarget.setAttribute('aria-expanded', String(isOpen));
        this.toggleTarget.setAttribute(
            'aria-label',
            isOpen ? this.closeLabelValue : this.openLabelValue,
        );

        this.overlayTarget.setAttribute('aria-hidden', String(!isOpen));
    }

    /** @this {SidebarControllerContext} */
    toggle() {
        this.openValue = !this.openValue;
    }

    /** @this {SidebarControllerContext} */
    open() {
        this.openValue = true;
    }

    /** @this {SidebarControllerContext} */
    close() {
        this.openValue = false;
    }
}
