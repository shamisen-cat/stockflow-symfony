// @ts-check

import { Controller } from '@hotwired/stimulus';

/**
 * @typedef EmailGeneratorControllerContext
 *
 * @property {HTMLElement} element
 * @property {HTMLInputElement[]} lengthTargets
 * @property {string} inputIdValue
 *
 * @property {function(): void} generate
 * @property {function(): number} selectedLength
 * @property {function(number): string} randomHex
 * @property {function(): HTMLInputElement} inputElement
 */

/**
 * Generate random hex email local-parts for dev form input.
 *
 * Values:
 * - inputId: id of the email input to fill
 *
 * Targets:
 * - length: radio options whose values are the hex lengths
 *
 * Actions:
 * - generate: write a new random email to input
 *
 * @extends {Controller}
 */
export default class extends Controller {
    static targets = ['length'];

    static values = {
        inputId: String,
    };

    /** @this {EmailGeneratorControllerContext} */
    generate() {
        const length = this.selectedLength();
        const localPart = this.randomHex(length);
        const input = this.inputElement();

        input.value = `${localPart}@example.com`;

        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    /** @this {EmailGeneratorControllerContext} */
    selectedLength() {
        // prettier-ignore
        const selected = this.lengthTargets.find(
            (target) => target.checked
        ) ?? this.lengthTargets[0];

        if (!selected) {
            throw new Error('email-generator requires at least one length target');
        }

        const parsed = Number.parseInt(selected.value, 10);

        if (Number.isNaN(parsed) || parsed <= 0) {
            throw new Error(
                `email-generator length target must be positive integer, got "${selected.value}"`,
            );
        }

        return parsed;
    }

    /**
     * @this {EmailGeneratorControllerContext}
     * @param {number} hexLength
     * @returns {string}
     */
    randomHex(hexLength) {
        const byteLength = Math.ceil(hexLength / 2);
        const bytes = new Uint8Array(byteLength);

        crypto.getRandomValues(bytes);

        // prettier-ignore
        const hex = Array.from(
            bytes,
            (byte) => byte.toString(16).padStart(2, '0')
        ).join('');

        return hex.slice(0, hexLength);
    }

    /** @this {EmailGeneratorControllerContext} */
    inputElement() {
        if (this.inputIdValue === '') {
            throw new Error('email-generator requires inputId');
        }

        const candidate = document.getElementById(this.inputIdValue);

        if (!(candidate instanceof HTMLInputElement)) {
            throw new Error(
                `email-generator inputId "${this.inputIdValue}" must match input element`,
            );
        }

        return candidate;
    }
}
