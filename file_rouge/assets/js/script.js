/**
 * Script JavaScript — Gestion de Finances Personnelles
 * Confirmations, validations de formulaires et interactions UX
 */

document.addEventListener("DOMContentLoaded", () => {
    // 1. Gestion des alertes flash : disparition automatique après 5 secondes
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach((alert) => {
        const closeBtn = alert.querySelector(".alert-close");
        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                alert.style.transition = "opacity 0.3s ease, transform 0.3s ease";
                alert.style.opacity = "0";
                alert.style.transform = "translateY(-10px)";
                setTimeout(() => alert.remove(), 300);
            });
        }

        // Disparition douce automatique
        setTimeout(() => {
            if (document.body.contains(alert)) {
                alert.style.transition = "opacity 0.5s ease, transform 0.5s ease";
                alert.style.opacity = "0";
                alert.style.transform = "translateY(-10px)";
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    });

    // 2. Confirmation dynamique de suppression
    // Tout lien ou bouton avec l'attribut data-confirm déclenche une confirmation
    document.querySelectorAll("[data-confirm]").forEach((el) => {
        el.addEventListener("click", (e) => {
            const message = el.getAttribute("data-confirm") || "Êtes-vous sûr de vouloir effectuer cette action ?";
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });

    // 3. Validation des formulaires côté client
    const forms = document.querySelectorAll("form.js-validate");
    forms.forEach((form) => {
        form.addEventListener("submit", (e) => {
            let isValid = true;
            const requiredInputs = form.querySelectorAll("[required]");

            // Vérification des champs requis
            requiredInputs.forEach((input) => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = "var(--danger)";
                } else {
                    input.style.borderColor = "";
                }
            });

            // Validation des montants (positif uniquement)
            const montantInput = form.querySelector('input[name="montant"]');
            if (montantInput) {
                const val = parseFloat(montantInput.value);
                if (isNaN(val) || val <= 0) {
                    isValid = false;
                    alert("Le montant doit être un nombre positif supérieur à zéro.");
                    montantInput.focus();
                    montantInput.style.borderColor = "var(--danger)";
                }
            }

            // Validation des emails
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput && emailInput.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    isValid = false;
                    alert("Veuillez saisir une adresse email valide.");
                    emailInput.focus();
                    emailInput.style.borderColor = "var(--danger)";
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // 4. Assistance intelligente sur le formulaire de Transaction :
    // Lorsqu'une catégorie est sélectionnée, on pré-sélectionne le type logique (crédit ou débit)
    const categorieSelect = document.getElementById("id_categorie");
    const typeTransactionSelect = document.getElementById("type_transaction");

    if (categorieSelect && typeTransactionSelect) {
        categorieSelect.addEventListener("change", () => {
            const selectedOption = categorieSelect.options[categorieSelect.selectedIndex];
            const suggestedType = selectedOption.getAttribute("data-type"); // 'depense' ou 'revenu'
            if (suggestedType === "revenu") {
                typeTransactionSelect.value = "credit";
            } else if (suggestedType === "depense") {
                typeTransactionSelect.value = "debit";
            }
        });
    }
});

/**
 * Fonction globale utilisable en onclick si nécessaire
 */
function confirmDelete(event, customMessage) {
    const message = customMessage || "Êtes-vous sûr de vouloir supprimer cet enregistrement ? Cette action est irréversible.";
    if (!confirm(message)) {
        if (event) event.preventDefault();
        return false;
    }
    return true;
}
