<?php
/**
 * Wpal Modal HTML Template
 *
 * @package Wpal
 * @subpackage Core
 * @since 1.0.0
 */

?>

<!-- Wpal Modal Wrapper -->
<div id="wpal-modal" x-data="wpal" x-show="modal" style="display: none;">

	<!-- Wpal Logo -->
	<div class="wpal-ribbon">
		<div class="wpal-ribbon-content" :class="{'wpal-ribbon-content-loading': spinner}">
			<div class="wpal-ribbon-logo"></div>
		</div>
	</div>

	<!-- Wpal input -->
	<input id="wpal-modal-input" type="text" x-model="term" @input="searchChange" autocomplete="off" @keydown="fixInputCursor">

	<!-- Wpal Spinner -->
	<!-- <div class="spinner" :class="{'is-active': spinner}"></div> -->

	<!-- Results area -->
	<div class="wpal-modal-dropdown">

		<template x-for="(item, index) in results" :key="index">

			<div class="wpal-modal-dropdown-item" :class="{'wpal-selected': index === selection}" @mouseover="resultsMouseOver(index)">
				<!-- Dashicons icon -->
				<template x-if="item.icon && item.icon.includes('dashicons-')">
						<span class="dashicons-before" :class="[item.icon]"></span>
				</template>

				<!-- Base64 icon -->
				<template x-if="item.icon && item.icon.includes('base64')">
					<span class="wpal-icon-base64" :style="'background-image: url(' + item.icon + ')'"></span>
				</template>

				<!-- Result label -->
				<a :href="item.link">
					<span x-text="item.label"></span>
				</a>

				<!-- Result type -->
				<template x-if="item.type">
					<div class="wpal-result-type">
						(<span x-text="item.type"></span>)
					</div>
				</template>
			</div>
		</template>

	</div>

</div>
