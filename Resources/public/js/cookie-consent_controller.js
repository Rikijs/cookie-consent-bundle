import { Controller } from '@hotwired/stimulus';

/**
 * Cookie Consent Controller
 * Handles the display and submission of the cookie consent.
 */
export default class extends Controller {
    static targets = ['cookieContainer', "cookieForm", 'cookieCategoryDetails', 'toggleHide', 'toggleShow'];
    static values = {
        submitSuccessEvent: { type: String, default: 'cookie-consent-form-submit-successful' }
    }

    connect() {
        console.log('[CookieConsent] Controller connected!');
    }

    toggleDetails() {
        if (!this.hasCookieCategoryDetailsTarget) return;

        const isHidden = getComputedStyle(this.cookieCategoryDetailsTarget).display === 'none';
        this.cookieCategoryDetailsTarget.style.display = isHidden ? 'block' : 'none';

        if (this.hasToggleHideTarget) {
            this.toggleHideTarget.style.display = isHidden ? 'block' : 'none';
        }
        if (this.hasToggleShowTarget) {
            this.toggleShowTarget.style.display = isHidden ? 'none' : 'block';
        }
    }

    async submitForm(event) {
        event.preventDefault();
        const clickedButton = event.currentTarget;
        const form = this.hasCookieFormTarget ? this.cookieFormTarget : clickedButton.form;

        if (!form) return;

        try {
            const action = form.action ? new URL(form.action, window.location.origin).href : window.location.href;
            const response = await fetch(action, {
                method: 'POST',
                cache: 'no-store',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: this.serializeForm(form, clickedButton),
            });

            const assertOk = (response) => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            };
            assertOk(response);

            this.cookieContainerTarget.style.display = "none";

            // Dispatch success event
            this.dispatch('submit-successful', { detail: clickedButton });
            document.dispatchEvent(new CustomEvent(this.submitSuccessEventValue, {
                detail: clickedButton
            }));

            // Reload to apply cookie changes
            window.location.reload();
        } catch (error) {
            console.error('[CookieConsent] Error submitting form:', error);
        } finally {
            document.body.style.marginTop = '';
            document.body.style.marginBottom = '';
        }
    }

    serializeForm(form, clickedButton) {
        const formData = new FormData(form);
        if (clickedButton?.name) {
            formData.append(clickedButton.name, clickedButton.value || "");
        }
        return new URLSearchParams(formData).toString();
    }
}
