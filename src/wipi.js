import 'alpinejs'
import './wipi.scss'

function wipi() {
	return {
		modal: false,
		term: '',

		init(nextTick) {
			const self = this;

			// Hotkeys.
			document.addEventListener('keyup', function(e) {
				// Toggle modal.
				if ( e.ctrlKey && ' ' === e.key ) {
					self.modal = !self.modal;

					if ( self.modal ) {
						nextTick(() => {
							document.getElementById('wipi-modal-input').focus();
						});
					}
				}

				// Esc - close modal.
				if ( 'Escape' === e.key ) {
					self.modal = false;
				}
			}, false);

			// Mouse click outside Wipi to close it.
			document.addEventListener('click', function(e) {
				if ( ! document.getElementById('wipi-modal').contains(e.target) ) {
					self.modal = false;
				}
			}, false);
		},

		getAdminMenu() {
			var adminMenu = document.querySelectorAll('#adminmenu a');

			console.log(adminMenu);

			for(var i = 0; i < adminMenu.length; i += 1) {
				var href = adminMenu[i].href;
				var label = adminMenu[i].innerText.replace(/\n|\r/g, "").trim();
				var classes = adminMenu[i].className;
				console.log(label);
				
			}
		},

		searchChange() {
			console.log('Term: ', this.term);
		}
	}
}

window.wipi = wipi;