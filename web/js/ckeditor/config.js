/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
    //config.uiColor = '#AADC6E';
    config.toolbarCanCollapse = true; //ツールバー折りたたみ操作
  	config.language = 'ja';
    config.width = '700';
    config.height = '500';
    config.enterMode = 2; // <br>を挿入
    //config.enterMode = 3; // <div>を挿入
    config.coreStyles_italic = {element : 'span',  styles : { 'font-style': 'oblique' } }; //斜体文字のタグをデフォルト<em>から<span>に変更しstyle付与
    config.coreStyles_bold = {element: 'span', styles: { 'font-weight': 'bold' } }; //太字のタグをデフォルト<strong>から<span>に変更しstyle付与
    //ツールバー「フォントファミリー(Font)」
    config.font_names='メイリオ,Meiryo; "Yu Gothic Medium","游ゴシック Medium",YuGothic,"游ゴシック体",YuGothicM,"Yu Gothic"; ＭＳ Ｐゴシック; ＭＳ ゴシック; "游明朝","Yu Mincho",YuMincho; ＭＳ Ｐ明朝; ＭＳ 明朝; Arial/Arial, Helvetica, sans-serif; Comic Sans MS/Comic Sans MS, cursive; Courier New/Courier New, Courier, monospace; Georgia/Georgia, serif; Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif; Tahoma/Tahoma, Geneva, sans-serif; Times New Roman/Times New Roman, Times, serif; Trebuchet MS/Trebuchet MS, Helvetica, sans-serif; Verdana/Verdana, Geneva, sans-serif';

    config.toolbarGroups = [
     { name: 'clipboard',groups: [ 'clipboard', 'undo' ] },
     // { name: 'editing',groups: [ 'find', 'selection', 'spellchecker' ] },
     { name: 'links' },
     // { name: 'insert' },
     // { name: 'forms' },
     // { name: 'tools' },
     // { name: 'document',groups: [ 'mode', 'document', 'doctools' ] },
     // { name: 'others' },
     // '/',
     { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
     // { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
     { name: 'paragraph',   groups: [ 'list', 'align' ] },
     { name: 'styles' },
     { name: 'colors' },
     // { name: 'about' }
    ];

};
