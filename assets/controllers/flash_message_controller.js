// @ts-check

import { Controller } from '@hotwired/stimulus';

/**
 * @typedef FlashMessageControllerContext
 *
 * @property {HTMLElement} element
 *
 * @property {number} dismissAfterValue
 * @property {ReturnType<typeof setTimeout> | null} timerId
 * @property {number} remainingMs
 * @property {number | null} timerStartedAt
 *
 * @property {function(): void} connect
 * @property {function(): void} disconnect
 * @property {function(): void} dismiss
 * @property {function(string, Object=): CustomEvent} dispatch
 * @property {function(): void} pauseTimer
 * @property {function(): void} resumeTimer
 * @property {function(): void} startTimer
 * @property {function(): void} clearTimer
 */

/**
 * Single session flash alert (child controller).
 *
 * Attached to each role="alert" element in flash_messages.html.twig.
 * Values:
 * - dismissAfter: auto-dismiss delay in ms (0 = manual dismiss only)
 *
 * Actions:
 * - dismiss: removes the alert and dispatches flash-message:dismissed
 * - pauseTimer / resumeTimer: pause auto-dismiss on hover or focus
 *
 * @extends {Controller}
 */
export default class extends Controller {
    static values = {
        dismissAfter: { type: Number, default: 0 },
    };

    /** @type {ReturnType<typeof setTimeout> | null} */
    timerId = null;

    /** @type {number} */
    remainingMs = 0;

    /** @type {number | null} */
    timerStartedAt = null;

    /** @this {FlashMessageControllerContext} */
    connect() {
        if (this.dismissAfterValue <= 0) {
            return;
        }

        this.remainingMs = this.dismissAfterValue;
        this.startTimer();
    }

    /** @this {FlashMessageControllerContext} */
    disconnect() {
        this.clearTimer();
    }

    /** @this {FlashMessageControllerContext} */
    dismiss() {
        this.clearTimer();
        this.dispatch('dismissed', { bubbles: true });
        this.element.remove();
    }

    /** @this {FlashMessageControllerContext} */
    pauseTimer() {
        if (this.timerId === null) {
            return;
        }

        this.remainingMs -= Date.now() - (this.timerStartedAt ?? Date.now());
        this.clearTimer();
    }

    /** @this {FlashMessageControllerContext} */
    resumeTimer() {
        if (this.dismissAfterValue <= 0 || this.remainingMs <= 0) {
            return;
        }

        this.startTimer();
    }

    /** @this {FlashMessageControllerContext} */
    startTimer() {
        this.clearTimer();
        this.timerStartedAt = Date.now();
        this.timerId = setTimeout(() => this.dismiss(), this.remainingMs);
    }

    /** @this {FlashMessageControllerContext} */
    clearTimer() {
        if (this.timerId !== null) {
            clearTimeout(this.timerId);
            this.timerId = null;
        }

        this.timerStartedAt = null;
    }
}
