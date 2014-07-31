/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function(config) {
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
	config.filebrowserBrowseUrl = '/public/kcfinder-3.12/browse.php?opener=ckeditor&type=files';
	config.filebrowserImageBrowseUrl = '/public/kcfinder-3.12/browse.php?opener=ckeditor&type=images';
	config.filebrowserFlashBrowseUrl = '/public/kcfinder-3.12/browse.php?opener=ckeditor&type=flash';
	config.filebrowserUploadUrl = '/public/kcfinder-3.12/upload.php?opener=ckeditor&type=files';
	config.filebrowserImageUploadUrl = '/public/kcfinder-3.12/upload.php?opener=ckeditor&type=images';
	config.filebrowserFlashUploadUrl = '/public/kcfinder-3.12/upload.php?opener=ckeditor&type=flash';
};