import 'alpinejs'
import './wipi.scss'

function wipi() {
	return {
		modal: false,
		term: '',
		adminMenu: [],
		results: [],
		selection: 0,
		serverSearchTimeout: 0,

		init(nextTick) {
			const self = this;

			//this.adminMenu = wipiData.admin_menu;
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

				// Enter key.
				if ( self.modal && 'Enter' === e.key && self.results[self.selection] ) {
					window.location = self.results[self.selection].href;
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
			var icon = '';
			for(let i = 0; i < adminMenuDOM.length; i += 1) {
				var href = adminMenuDOM[i].href;
				var label = adminMenuDOM[i].innerText.replace(/\n|\r/g, "").trim();
				var classes = adminMenuDOM[i].className;				
				
				isParent = classes.includes('menu-top');
				if ( isParent ) {
					parentLabel = label;
					var iconDOM = adminMenuDOM[i].querySelector('.wp-menu-image');
					if(iconDOM) {
						var iconClasses = iconDOM.className.replace('dashicons-before', '');
						var dashicon = iconClasses.match(/(dashicons-[a-z0-9-]*)/g);
						if(dashicon) {
							icon = dashicon[0];
						} else {
							var iconImg = iconDOM.querySelector('img');
							if(iconImg) {
								icon = iconImg.src;
							}
						}	
					}
				} else {
					label = `${parentLabel} - ${label}`;
				}

				const item = {
					label,
					labelLC: label.toLowerCase(),
					href,
					icon
				}
				adminMenu.push(item);
			}

			return adminMenu;
		},

		searchChange() {
			const self = this;

			const term = this.term.toLowerCase().trim();

			if ( term.length === 0 ) {
				this.results = [];
				return;
			}

			const termParts = term.split(' ');

			this.results = this.adminMenu.filter(item => {
				const allExist = termParts.every(termPart => item.labelLC.includes(termPart));
				return allExist;
			});
			this.selection = 0;

			// search posts.
			const termServer = term.replace(' ', '+');
			clearTimeout(this.serverSearchTimeout);
			this.serverSearchTimeout = setTimeout(async () => {
				const response = await fetch(`${wipiData.rest}/search/${termServer}`, {
					headers: { 
						"X-WP-Nonce": wipiData.nonce,
						"Content-Type": "application/json;charset=utf-8"
					}
				});
				const responseJson = await response.json();
				responseJson.forEach(item => {
					self.results.push(item);
				});
			}, 300);
		},

		resultsMouseOver(index) {
			this.selection = index;
		},

		fixInputCursor(e) {
			if ( 'ArrowUp' === e.key || 'ArrowDown' === e.key ) {
				return false;
			}
		}
	}
}

window.wipi = wipi;