<?php
/**
 * Wipi Modal HTML Template
 *
 * @package Wipi
 * @subpackage Core
 * @since 1.0.0
 */

?>

<!-- Wipi Modal Wrapper -->
<div id="wipi-modal" x-data="wipi()" x-init="init($nextTick)" x-show="modal" style="display: none;">

	<!-- Wipi input -->
	<input id="wipi-modal-input" type="text" x-model="term" @input="searchChange" autocomplete="off" @keydown="fixInputCursor">

	<!-- Results area -->
	<div class="wipi-modal-dropdown">

		<template x-for="(item, index) in results" :key="index">

			<div class="wipi-modal-dropdown-item" :class="{'wipi-selected': index === selection}" @mouseover="resultsMouseOver(index)">

				<!-- Dashicons icon -->
				<template x-if="item.icon && item.icon.includes('dashicons-')">
						<span class="dashicons-before" :class="[item.icon]"></span>
				</template>

				<!-- Base64 icon -->
				<template x-if="item.icon && item.icon.includes('base64')">
					<span class="wipi-icon-base64" :style="'background-image: url(' + item.icon + ')'"></span>
				</template>

				<!-- Result label -->
				<a :href="item.link">
					<template x-if="item.prefix">
						[<span x-text="item.prefix"></span>] 
					</template>
					<span x-text="item.label"></span>
				</a>
			</div>
		</template>

	</div>

</div>
