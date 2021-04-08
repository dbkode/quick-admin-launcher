<div id="wipi-modal" x-data="wipi()" x-init="init($nextTick)" x-show="modal" style="display: none;">
	<input id="wipi-modal-input" type="text" x-model="term" @input="searchChange">
	<div class="wipi-modal-dropdown">
		<div class="wipi-modal-dropdown-item">Posts</div>
		<div class="wipi-modal-dropdown-item">Pages</div>
		<div class="wipi-modal-dropdown-item">Settings</div>
	</div>
</div>