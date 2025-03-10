import React from '@wordpress/element';
import { createRoot } from '@wordpress/element';
import QuickalModal from './QuickalModal';

const getAdminMenu = () => {
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
};

document.addEventListener('DOMContentLoaded', () => {
	const root = createRoot(
		document.getElementById( 'quickal-modal-root' )
	);

	const quickalData = window.quickalData;
	quickalData.hotkey.alt = quickalData.hotkey.alt ? true : false;
	quickalData.hotkey.ctrl = quickalData.hotkey.ctrl ? true : false;
	quickalData.hotkey.shift = quickalData.hotkey.shift ? true : false;
	quickalData.hotkey.meta = quickalData.hotkey.meta ? true : false;

	quickalData.adminMenu = getAdminMenu();

	if( quickalData.extra_items.length > 0 ) {
		quickalData.adminMenu = [...quickalData.adminMenu, ...quickalData.extra_items];
	}

	root.render( <QuickalModal quickalData={quickalData} /> );
});
