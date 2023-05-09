<?php
/**
 * QuickAL Modal HTML Template
 *
 * @package QuickAL
 * @subpackage Core
 * @since 1.0.0
 */

?>

<!-- QuickAL Modal Wrapper -->
<div id="quickal-modal-wrapper" x-data="quickal" x-show="modal" style="display: none;">

	<div id="quickal-modal">

	<!-- QuickAL Logo -->
	<div class="quickal-ribbon">
		<div class="quickal-ribbon-content" :class="{'quickal-ribbon-content-loading': spinner}">
			<div class="quickal-ribbon-logo"></div>
		</div>
	</div>

	<!-- QuickAL input -->
	<input id="quickal-modal-input" type="text" x-model="term" @input="searchChange" autocomplete="off" @keydown="fixInputCursor" placeholder="<?php esc_html_e( 'Search any admin tool or content...', 'quickal' ); ?>">

	<!-- QuickAL Spinner -->
	<!-- <div class="spinner" :class="{'is-active': spinner}"></div> -->

	<!-- Results area -->
	<div class="quickal-modal-dropdown">

		<template x-for="(item, index) in results" :key="index">

			<div class="quickal-modal-dropdown-item" :class="{'quickal-selected': index === selection}" @mouseover="resultsMouseOver(index)">
				<!-- Dashicons icon -->
				<template x-if="item.icon && item.icon.includes('dashicons-')">
						<span class="dashicons-before" :class="[item.icon]"></span>
				</template>

				<!-- Base64 icon -->
				<template x-if="item.icon && item.icon.includes('base64')">
					<span class="quickal-icon-base64" :style="'background-image: url(' + item.icon + ')'"></span>
				</template>

				<!-- Result label -->
				<a :href="item.link">
					<span x-text="item.label"></span>
				</a>

				<!-- Result type -->
				<template x-if="item.type">
					<div class="quickal-result-type">
						(<span x-text="item.type"></span>)
					</div>
				</template>
			</div>
		</template>

	</div>

	</div>

</div>
