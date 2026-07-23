// @ts-check

import { Controller } from '@hotwired/stimulus';

/**
 * @typedef PasswordVisibilityControllerContext
 *
 * @property {HTMLElement} element
 * @property {HTMLInputElement} inputTarget
 * @property {HTMLButtonElement} toggleTarget
 *
 * @property {boolean} visibleValue
 * @property {string} showLabelValue
 * @property {string} hideLabelValue
 *
 * @property {function(): void} toggle
 * @property {function(): void} visibleValueChanged
 */

/**
 * Toggle an input between password and plain text.
 *
 * Icon visibility is driven by aria-pressed via Tailwind
 * group-aria-pressed/toggle variants (same pattern as sidebar).
 *
 * Values:
 * - visible: whether the password is shown as plain text
 * - showLabel: aria-label / title when the password is hidden
 * - hideLabel: aria-label / title when the password is visible
 *
 * Targets:
 * - input: password input whose type is toggled
 * - toggle: button that triggers the toggle
 *
 * Actions:
 * - toggle: flip visible state
 *
 * @extends {Controller}
 */
export default class extends Controller {
    static targets = ['input', 'toggle'];

    static values = {
        visible: { type: Boolean, default: false },
        showLabel: String,
        hideLabel: String,
    };

    /** @this {PasswordVisibilityControllerContext} */
    toggle() {
        this.visibleValue = !this.visibleValue;
    }

    /** @this {PasswordVisibilityControllerContext} */
    visibleValueChanged() {
        const visible = this.visibleValue;
        this.inputTarget.type = visible ? 'text' : 'password';
        this.toggleTarget.setAttribute('aria-pressed', String(visible));

        const label = visible ? this.hideLabelValue : this.showLabelValue;
        this.toggleTarget.setAttribute('title', label);
        this.toggleTarget.setAttribute('aria-label', label);
    }
}
