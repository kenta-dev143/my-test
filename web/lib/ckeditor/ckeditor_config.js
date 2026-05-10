/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	config.language = 'ja';
	// config.uiColor = '#AADC6E';

    CKEDITOR.config.toolbar =
    [
        ['Source','-','Save','NewPage','Preview','Templates','ShowBlocks','-','Undo','Redo'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker','Scayt'],
        ['Find','Replace','-','SelectAll','RemoveFormat'],
        ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
        ['Table','HorizontalRule'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
        ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
        ['TextColor','BGColor'],
        ['Link','Unlink','Anchor'],
        ['Smiley','SpecialChar'],
        ['Maximize','ShowBlocks'],
        '/',
        ['Styles','Format','Font','FontSize'],
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript']
    ];

    CKEDITOR.config.font_names='ＭＳ Ｐゴシック;ＭＳ Ｐ明朝;ＭＳ ゴシック;ＭＳ 明朝;Arial/Arial, Helvetica, sans-serif;Comic Sans MS/Comic Sans MS, cursive;Courier New/Courier New, Courier, monospace;Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Tahoma/Tahoma, Geneva, sans-serif;Times New Roman/Times New Roman, Times, serif;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;Verdana/Verdana, Geneva, sans-serif';
    CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;

};
