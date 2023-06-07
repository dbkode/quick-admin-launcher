<?php
/**
 * QuickAL Modal HTML Template
 *
 * quickal-on:package QuickAL
 * quickal-on:subpackage Core
 * quickal-on:since 1.0.0
 */

?>

<!-- QuickAL Modal Wrapper -->
<div id="quickal-modal-wrapper" quickal-data="quickal" quickal-show="modal" style="display: none;">

	<div id="quickal-modal">

	<!-- QuickAL Logo -->
	<div class="quickal-ribbon">
		<div class="quickal-ribbon-content" quickal-bind:class="{'quickal-ribbon-content-loading': spinner}">
			<div class="quickal-ribbon-logo"></div>
		</div>
	</div>

	<!-- QuickAL input -->
	<input id="quickal-modal-input" type="text" quickal-model="term" quickal-on:input="searchChange" autocomplete="off" quickal-on:keydown="fixInputCursor" placeholder="<?php esc_html_e( 'Search any admin tool or content...', 'quickal' ); ?>">

	<!-- QuickAL Spinner -->
	<!-- <div class="spinner" quickal-bind:class="{'is-active': spinner}"></div> -->

	<!-- Results area -->
	<div class="quickal-modal-dropdown">

		<template quickal-for="(item, index) in results" :key="index">

			<div class="quickal-modal-dropdown-item" quickal-bind:class="{'quickal-selected': index === selection}" quickal-on:mouseover="resultsMouseOver(index)">
				<!-- Dashicons icon -->
				<template quickal-if="item.icon && item.icon.includes('dashicons-')">
						<span class="dashicons-before" quickal-bind:class="[item.icon]"></span>
				</template>

				<!-- Base64 icon -->
				<template quickal-if="item.icon && item.icon.includes('base64')">
					<span class="quickal-icon-base64" quickal-bind:style="'background-image: url(' + item.icon + ')'"></span>
				</template>

				<!-- Result label -->
				<a :href="item.link">
					<span quickal-text="item.label"></span>
				</a>

				<!-- Result type -->
				<template quickal-if="item.type">
					<div class="quickal-result-type">
						(<span quickal-text="item.type"></span>)
					</div>
				</template>
			</div>
		</template>

	</div>

	</div>

</div>
