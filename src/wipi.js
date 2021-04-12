import 'alpinejs'
import './wipi.scss'

function wipi() {
	return {
		modal: false,
		term: '',
		adminMenu: [],
		results: [],

		init(nextTick) {
			const self = this;

			this.adminMenu = this.getAdminMenu();

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
			const adminMenuDOM = document.querySelectorAll('#adminmenu a');

			let adminMenu = [];
			let parentLabel = '';
			let isParent = false;
			for(let i = 0; i < adminMenuDOM.length; i += 1) {
				var href = adminMenuDOM[i].href;
				var label = adminMenuDOM[i].innerText.replace(/\n|\r/g, "").trim();
				var classes = adminMenuDOM[i].className;
				
				isParent = classes.includes('wp-has-submenu');
				if ( isParent ) {
					parentLabel = label;
				} else {
					label = `${parentLabel} - ${label}`;
				}

				const item = {
					label,
					labelLC: label.toLowerCase(),
					href
				}
				adminMenu.push(item);
			}

			return adminMenu;
		},

		searchChange() {
			const term = this.term.toLowerCase();

			this.results = this.adminMenu.filter(item => {
				return item.labelLC.includes(term);
			});
		}
	}
}

window.wipi = wipi;