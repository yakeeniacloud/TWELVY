class TrackingPathUser {
	constructor() {
		this.baseUrl =
			"https://www.prostagespermis.fr/src/payment/ajax/tracking_path_user_api.php"; // URL du fichier PHP qui gère le tracking
	}

	/**
	 * Envoie une requête AJAX pour enregistrer une étape
	 * @param {string} etape - Nom de l'étape (ex: "affichage_form", "erreur_form", etc.)
	 * @param {string} [email] - Optionnel : email de l'utilisateur si disponible
	 */
	async addTracking(etape, whereclause = "session", id_stagiaire = null) {
		await $.ajax({
			url: this.baseUrl,
			type: "POST",
			data: {
				action: "add_tracking",
				etape: etape,
				whereclause: whereclause,
				id_stagiaire: id_stagiaire,
			},
			success: function (response) {
				console.log("Tracking envoyé :", response);
			},
			error: function (xhr, status, error) {
				console.error("Erreur AJAX :", status, error);
			},
		});
	}

	/**
	 * Capture l'abandon de formulaire en cas de fermeture de page
	 * @param {string} etape - Étape d'abandon à enregistrer
	 */
	enregistrerAbandon(etape) {
		window.addEventListener("beforeunload", () => {
			navigator.sendBeacon(
				this.baseUrl,
				JSON.stringify({
					action: "enregistrerEtape",
					etape: etape,
				})
			);
		});
	}
}
