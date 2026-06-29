// @ts-check

import { Controller } from '@hotwired/stimulus';

/**
 * @typedef FlashMessagesControllerContext
 *
 * @property {HTMLElement} element
 *
 * @property {function(): void} onDismissed
 */

/**
 * Session flash message wrapper (parent controller).
 *
 * Attached to the wrapper in flash_messages.html.twig.
 * Listens for flash-message:dismissed from child alerts.
 *
 * Actions:
 * - onDismissed: removes the wrapper when no alerts remain
 *
 * @extends {Controller}
 */
export default class extends Controller {
    /** @this {FlashMessagesControllerContext} */
    onDismissed() {
        queueMicrotask(() => {
            if (this.element.children.length === 0) {
                this.element.remove();
            }
        });
    }
}
