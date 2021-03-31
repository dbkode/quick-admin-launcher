import 'alpinejs'

function wipi() {
	return {
		modal: false,

		init() {
			const self = this;
			document.addEventListener('keyup', function(e) {
				if (e.ctrlKey && e.key === ' ') {
					console.log('SPACE!!!');
					self.modal = !self.modal;
				}
			}, false);
		}
	}
}

window.wipi = wipi;