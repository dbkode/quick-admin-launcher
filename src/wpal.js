/**
 * Wpal modal handler.
 *
 * Modal js code to handle the different user interactions.
 *
 * @since 1.0.0
 */

/**
 * Dependencies.
 */
import Alpine from 'alpinejs' 
import './wpal.scss'

window.Alpine = Alpine 

Alpine.data('wpal', () => ({
	/**
	 * Is modal visible.
	 *
	 * @since  1.0.0
	 *
	 * @type boolean
	 */
	modal: false,

	/**
	 * Input search term.
	 *
	 * @since 1.0.0
	 *
	 * @type string
	 */
	term: '',

	/**
	 * Current WP admin menu.
	 *
	 * @since 1.0.0
	 *
	 * @type array
	 */
	adminMenu: [],

	/**
	 * Search results.
	 *
	 * @since 1.0.0
	 *
	 * @type array
	 */
	results: [],

	/**
	 * Current search result selection.
	 *
	 * @since 1.0.0
	 *
	 * @type int
	 */
	selection: 0,

	/**
	 * Is spinner visible.
	 *
	 * @since  1.0.0
	 *
	 * @type boolean
	 */
		spinner: false,

	/**
	 * Server search timeout timer ID.
	 *
	 * @since 1.0.0
	 *
	 * @type int
	 */
	serverSearchTimeout: 0,

	/**
	 * Init.
	 *
	 * Initializes the AlpineJS instance.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	init() {
		const self = this;

		// Get WP admin menu.
		this.adminMenu = this.getAdminMenu();

		// Add any extra items added via filter.
		if ( wpalData.extra_items.length > 0 ) {
			Array.prototype.push.apply(this.adminMenu, wpalData.extra_items);
		}

		// Convert hotkey keys to booleans
		wpalData.hotkey.alt   = wpalData.hotkey.alt ? true : false;
		wpalData.hotkey.ctrl  = wpalData.hotkey.ctrl ? true : false;
		wpalData.hotkey.shift = wpalData.hotkey.shift ? true : false;
		wpalData.hotkey.meta  = wpalData.hotkey.meta ? true : false;

		// Add click event to admin bar button.
		document.querySelector('.wpal-admin-bar > a').addEventListener('click', (e) => {
			self.modal = !self.modal;
			if ( self.modal ) {
				setTimeout(() => {
					document.getElementById('wpal-modal-input').focus();
				}, 100);
			}
			e.preventDefault();
		});

		// Setup Hotkeys.
		document.addEventListener('keydown', function(e) {
			// Bail out if setting the hotkey on settings page.
			if( 'wpal_setting_hotkey_display' === document.activeElement.id ) {
				return;
			}

			// Toggle modal.
			if ( wpalData.hotkey.key === e.key 
				&& wpalData.hotkey.alt === e.altKey
				&& wpalData.hotkey.ctrl === e.ctrlKey
				&& wpalData.hotkey.shift === e.shiftKey
				&& wpalData.hotkey.meta === e.metaKey ) {
				self.modal = !self.modal;

				if ( self.modal ) {
					setTimeout(() => {
						document.getElementById('wpal-modal-input').focus();
					}, 100);
				}

				e.preventDefault();
			}

			// Esc - close modal.
			if ( self.modal && 'Escape' === e.key ) {
				self.modal = false;
				self.spinner = false;
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
				window.location = self.results[self.selection].link;
			}
		}, false);

		// Mouse click outside Wpal to close it.
		document.addEventListener('click', function(e) {
			if ( ! document.getElementById('wpal-modal').contains(e.target)
				&& ! document.querySelector('.wpal-admin-bar').contains(e.target) ) {
				self.modal = false;
				self.spinner = false;
			}
		}, false);
	},

	/**
	 * Get WP Admin Menu.
	 *
	 * Scrape WP admin menu items.
	 *
	 * @since 1.0.0
	 *
	 * @return array All WP admin menu items.
	 */
	getAdminMenu() {
		const adminMenuDOM = document.querySelectorAll('#adminmenu a');

		let adminMenu   = [];
		let parentLabel = '';
		let isParent    = false;
		var icon        = '';

		for(let i = 0; i < adminMenuDOM.length; i += 1) {
			var link    = adminMenuDOM[i].href;
			var label   = adminMenuDOM[i].innerText.replace(/\n|\r/g, "").trim();
			var classes = adminMenuDOM[i].className;				
			
			isParent = classes.includes('menu-top');
			if ( isParent ) {
				parentLabel = label;
				var iconDOM = adminMenuDOM[i].querySelector('.wp-menu-image');
				if(iconDOM) {
					var iconClasses = iconDOM.className.replace('dashicons-before', '');
					var dashicon    = iconClasses.match(/(dashicons-[a-z0-9-]*)/g);
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
				term: label.toLowerCase(),
				link,
				icon
			}
			adminMenu.push(item);
		}

		return adminMenu;
	},

	/**
	 * Search changed callback.
	 *
	 * Triggered when the search input changes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	searchChange() {
		const self = this;

		const term = this.term.toLowerCase().trim();

		if ( term.length === 0 ) {
			this.results = [];
			return;
		}

		const termParts = term.split(' ');

		this.results = this.adminMenu.filter(item => {
			const allExist = termParts.every(termPart => item.term.includes(termPart));
			return allExist;
		});
		this.selection = 0;

		// search posts.
		this.spinner = true;
		const termServer = term.replace(' ', '+');
		clearTimeout(this.serverSearchTimeout);
		this.serverSearchTimeout = setTimeout(async () => {
			const response = await fetch(`${wpalData.rest}/search/${termServer}`, {
				headers: { 
					"X-WP-Nonce": wpalData.nonce,
					"Content-Type": "application/json;charset=utf-8"
				}
			});
			const responseJson = await response.json();
			responseJson.forEach(item => {
				self.results.push(item);
			});
			this.spinner = false;
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

}))

Alpine.start()