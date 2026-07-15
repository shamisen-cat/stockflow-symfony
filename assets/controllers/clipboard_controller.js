// @ts-check

import { Controller } from '@hotwired/stimulus';

/**
 * @typedef ClipboardControllerContext
 *
 * @property {HTMLButtonElement} element
 * @property {HTMLElement} copyIconTarget
 * @property {HTMLElement} checkIconTarget
 *
 * @property {string} textValue
 * @property {string} copyLabelValue
 * @property {string} copiedLabelValue
 *
 * @property {ReturnType<typeof setTimeout> | null} resetTimerId
 *
 * @property {function(): void} connect
 * @property {function(): void} disconnect
 * @property {function(): Promise<void>} copy
 * @property {function(): void} showCopied
 * @property {function(): void} reset
 */

/** @type {number} */
const COPIED_RESET_MS = 2000;

/**
 * Copy text to the clipboard (button controller).
 *
 * Values:
 * - text: string to copy
 * - copyLabel: aria-label / title before copy
 * - copiedLabel: aria-label / title after successful copy
 *
 * Targets:
 * - copyIcon: default icon
 * - checkIcon: success icon (hidden until copy succeeds)
 *
 * Actions:
 * - copy: writes text to the clipboard and shows success feedback
 *
 * @extends {Controller}
 */
export default class extends Controller {
    static targets = ['copyIcon', 'checkIcon'];

    static values = {
        text: String,
        copyLabel: String,
        copiedLabel: String,
    };

    /** @type {ReturnType<typeof setTimeout> | null} */
    resetTimerId = null;

    /** @this {ClipboardControllerContext} */
    disconnect() {
        if (this.resetTimerId !== null) {
            clearTimeout(this.resetTimerId);
            this.resetTimerId = null;
        }
    }

    /** @this {ClipboardControllerContext} */
    async copy() {
        try {
            await navigator.clipboard.writeText(this.textValue);
            this.showCopied();
        } catch {
            // Clipboard API unavailable or permission denied; leave UI unchanged.
        }
    }

    /** @this {ClipboardControllerContext} */
    showCopied() {
        this.copyIconTarget.classList.add('hidden');
        this.checkIconTarget.classList.remove('hidden');

        this.element.setAttribute('title', this.copiedLabelValue);
        this.element.setAttribute('aria-label', this.copiedLabelValue);

        if (this.resetTimerId !== null) {
            clearTimeout(this.resetTimerId);
        }

        this.resetTimerId = setTimeout(() => this.reset(), COPIED_RESET_MS);
    }

    /** @this {ClipboardControllerContext} */
    reset() {
        this.copyIconTarget.classList.remove('hidden');
        this.checkIconTarget.classList.add('hidden');

        this.element.setAttribute('title', this.copyLabelValue);
        this.element.setAttribute('aria-label', this.copyLabelValue);

        this.resetTimerId = null;
    }
}
