import 'alpinejs'
import './wipi.scss'

function wipi() {
	return {
		modal: false,
		term: '',

		init(nextTick) {
			const self = this;
			document.addEventListener('keyup', function(e) {
				if ( e.ctrlKey && e.key === ' ' ) {
					self.modal = !self.modal;

					if ( self.modal ) {
						nextTick(() => {
							document.getElementById('wipi-modal-input').focus();
						});
					}
				}
			}, false);
		},

		searchChange() {
			console.log('Term: ', this.term);
		}
	}
}

window.wipi = wipi;