// alert("cookie_consent.js loaded");
// --- Helpers ---
const log = (...args) => console.log("[CookieConsent]", ...args);
const serializeForm = (form, clickedButton) => {
    const formData = new FormData(form);
    if (clickedButton?.name) formData.append(clickedButton.name, "");
    return new URLSearchParams(formData).toString();
};

// log("✅✅✅ Cookie Consent script loaded ✅✅✅");

// --- Polyfill (si nécessaire) ---
if (typeof window.CustomEvent !== "function") {
    window.CustomEvent = class CustomEventPolyfill extends Event {
        constructor(type, params = {}) {
            super(type, params);
            Object.assign(this, params);
        }
    };
}

// --- Main ---
document.addEventListener("DOMContentLoaded", () => {
    const cookieConsent = document.querySelector(".ch-cookie-consent");
    const manageBtn = document.querySelector(".ch-cookie-consent__manage-btn");
    const cookieConsentForm = document.querySelector(".ch-cookie-consent__form");
    const cookieConsentFormBtns = document.querySelectorAll(".ch-cookie-consent__btn");
    const categoryDetails = document.querySelector(".ch-cookie-consent__category-group");
    const detailsToggle = document.querySelector(".ch-cookie-consent__toggle-details");

    // --- Positionsment du consentement ---
    if (cookieConsent?.parentNode.nodeName === "BODY") {
        const height = cookieConsent.offsetHeight;
        const isTop = cookieConsent.classList.contains("ch-cookie-consent--top");
        document.body.style[isTop ? "marginTop" : "marginBottom"] = `${height}px`;
        Object.assign(cookieConsent.style, {
            position: isTop ? "absolute" : "fixed",
            top: isTop ? "0" : "",
            bottom: !isTop ? "0" : "",
            left: "0",
        });
    }

    // --- Gestion du bouton "Manage" ---
    manageBtn?.addEventListener("click", () => {
        // log("Manage cookie button clicked");
        if (!cookieConsent) return;
        const isHidden = getComputedStyle(cookieConsent).display === "none";
        cookieConsent.style.display = isHidden ? "block" : "none";
        manageBtn.style.display = isHidden ? "none" : "block";
    });

    // --- Soumission du formulaire ---
    if (cookieConsentForm) {
        console.warn("cookieConsentForm btn", cookieConsentFormBtns);
        cookieConsentFormBtns.forEach((btn) => {
            btn.addEventListener("click", async (event) => {
                // alert("click");
                // log("Form button clicked");
                event.preventDefault();
                try {
                    const formAction = cookieConsentForm.action ? new URL(cookieConsentForm.action, window.location.origin).href : window.location.href;
                    const response = await fetch(formAction, {
                        method: "POST",
                        cache: "no-store",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: serializeForm(cookieConsentForm, event.target),
                    });

                    const assertOk = (response) => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    };
                    assertOk(response);

                    // log("Form submitted successfully");
                    cookieConsent.style.display = "none";
                    manageBtn?.removeAttribute("style");
                    document.dispatchEvent(new CustomEvent("cookie-consent-form-submit-successful", { detail: event.target }));
                    //window.location.href = window.location.href; // Plus fiable que reload()
                    window.location.reload()
                } catch (error) {
                    // log("Error submitting form:", error);
                    // Optionnel: afficher une erreur à l'utilisateur
                } finally {
                    document.body.style.marginTop = "";
                    document.body.style.marginBottom = "";
                }
            });
        });
    }

    // --- Toggle des détails ---
    if (categoryDetails && detailsToggle) {
        detailsToggle.addEventListener("click", () => {
            const isHidden = categoryDetails.style.display !== "block";
            categoryDetails.style.display = isHidden ? "block" : "none";
            detailsToggle.querySelector(".ch-cookie-consent__toggle-details-hide").style.display = isHidden ? "block" : "none";
            detailsToggle.querySelector(".ch-cookie-consent__toggle-details-show").style.display = isHidden ? "none" : "block";
        });
    }
});
