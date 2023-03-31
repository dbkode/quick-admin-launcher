/**
 * Wipi modal handler.
 *
 * Modal js code to handle the different user interactions.
 *
 * @since 1.0.0
 */

/**
 * Dependencies.
 */
import Alpine from 'alpinejs' 
import './wipi.scss'

window.Alpine = Alpine 



//document.addEventListener('alpine:init', () => {
	Alpine.data('wipi', () => ({
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
		setup() {
			const self = this;

			// Get WP admin menu.
			this.adminMenu = this.getAdminMenu();

			// Add any extra items added via filter.
			if ( wipiData.extra_items.length > 0 ) {
				Array.prototype.push.apply(this.adminMenu, wipiData.extra_items);
			}

			// Convert hotkey keys to booleans
			wipiData.hotkey.alt   = wipiData.hotkey.alt ? true : false;
			wipiData.hotkey.ctrl  = wipiData.hotkey.ctrl ? true : false;
			wipiData.hotkey.shift = wipiData.hotkey.shift ? true : false;
			wipiData.hotkey.meta  = wipiData.hotkey.meta ? true : false;

			// Setup Hotkeys.
			document.addEventListener('keyup', function(e) {
				// Bail out if setting the hotkey on settings page.
				if( 'wipi_setting_hotkey_display' === document.activeElement.id ) {
					return;
				}

				// Toggle modal.
				if ( wipiData.hotkey.key === e.key 
					&& wipiData.hotkey.alt === e.altKey
					&& wipiData.hotkey.ctrl === e.ctrlKey
					&& wipiData.hotkey.shift === e.shiftKey
					&& wipiData.hotkey.meta === e.metaKey ) {
					self.modal = !self.modal;

					if ( self.modal ) {
						document.getElementById('wipi-modal-input').focus();
					}
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

			// Mouse click outside Wipi to close it.
			document.addEventListener('click', function(e) {
				if ( ! document.getElementById('wipi-modal').contains(e.target) ) {
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
//})

Alpine.start()