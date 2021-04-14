import 'alpinejs'
import './wipi.scss'

function wipi() {
	return {
		modal: false,
		term: '',
		adminMenu: [],
		results: [],
		selection: 0,

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
				if ( self.modal && 'Escape' === e.key ) {
					self.modal = false;
				}

				// Down key.
				if ( self.modal && 'ArrowDown' === e.key ) {
					if ( self.selection + 1 < self.results.length ) {
						self.selection += 1;
					} else {
						self.selection = 0;
					}
				}

				// Up key.
				if ( self.modal && 'ArrowUp' === e.key ) {
					if ( self.selection - 1 >= 0 ) {
						self.selection -= 1;
					} else {
						self.selection = self.results.length - 1;
					}
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
			this.selection = 0;
		}
	}
}

window.wipi = wipi;