<div id="wipi-modal" x-data="wipi()" x-init="init($nextTick)" x-show="modal" style="display: none;">
	<input id="wipi-modal-input" type="text" x-model="term" @input="searchChange" autocomplete="off">
	<div class="wipi-modal-dropdown">
		<template x-for="(item, index) in results" :key="index">
			<div class="wipi-modal-dropdown-item" :class="{'wipi-selected': index === selection}" @mouseover="resultsMouseOver(index)">
				<a :href="item.href" x-text="item.label"></a>
			</div>
		</template>
	</div>
</div>