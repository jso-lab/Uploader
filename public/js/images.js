windows.onload = () => {
	//gestion des boutons
	let links = document.querySelectorAll('[data-delete]');

	// on boucle sur links
	for (link of links) {
		//On écoute le clic
		link.addEventlistener('click', function (e) {
			//on empêche la navigation
			e.preventDefault();

			//On demande confirmation
			if (confirm('Voulez-vous supprimer cette image?')) {
				//On envoie une requête Ajax vers le href du lien avec la méthode DELETE
				fetch(this.getAttribute('href'), {
					method: 'DELETE',
					header: {
						'X-Requested-With': 'XMLHttpRequest',
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({ _token: this.dataset._token }),
				}).then(
					//On recupère la réponse en json
					(response) =>
						response
							.json()
							.then((data) => {
								if (data.success) this.parentElement.remove();
								else alert(data.error);
							})
							.catch((e) => alert(e))
				);
			}
		});
	}
};
