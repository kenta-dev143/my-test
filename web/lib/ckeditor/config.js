/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here. For example:
    // config.language = 'ja';
    // config.uiColor = '#AADC6E';
};

CKEDITOR.config.enterMode = 2;      //デフォルトのキー操作時の改行タグ挿入を<p>から<br>に変更
CKEDITOR.config.shiftEnterMode = 1; //shift+Enterキーでのデフォルト「改行<br>」を「<p>タグ」に変更