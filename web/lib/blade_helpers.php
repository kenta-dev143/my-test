<?php
/**
 * Blade テンプレート用ヘルパー関数
 * Smarty プラグインの機能を BladeOne テンプレートから直接呼び出せる形で提供する
 */

// -----------------------------------------------------------------------
// エスケープ共通処理
// -----------------------------------------------------------------------

/**
 * HTML エンティティを保持しつつ特殊文字をエスケープする
 * Smarty の smarty_function_escape_special_chars() 相当
 */
function blade_escape_special_chars(string $string): string {
    $string = preg_replace('!&(#?\w+);!', '%%%BLADE_START%%%\\1%%%BLADE_END%%%', $string);
    $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    $string = str_replace(['%%%BLADE_START%%%', '%%%BLADE_END%%%'], ['&', ';'], $string);
    return $string;
}

// -----------------------------------------------------------------------
// {html_options} 相当
// -----------------------------------------------------------------------

/**
 * <option> タグ群を生成する
 * Smarty の {html_options} タグ相当
 *
 * @param array $params
 *   - options   : 連想配列 [value => label]
 *   - values    : 値の配列（output と組み合わせて使用）
 *   - output    : 表示テキストの配列
 *   - selected  : 選択値（文字列または配列）
 *   - name      : select タグの name 属性（指定時は <select> タグごと出力）
 * @return string
 */
function blade_html_options(array $params): string {
    $name     = $params['name'] ?? null;
    $values   = isset($params['values'])  ? array_values((array)$params['values'])  : null;
    $options  = isset($params['options']) ? (array)$params['options']               : null;
    $output   = isset($params['output'])  ? array_values((array)$params['output'])  : null;
    $selected = isset($params['selected'])
        ? array_map('strval', array_values((array)$params['selected']))
        : [];

    // extra 属性（name/options/values/output/selected 以外）
    $extra = '';
    $skip  = ['name', 'options', 'values', 'output', 'selected'];
    foreach ($params as $k => $v) {
        if (!in_array($k, $skip) && !is_array($v)) {
            $extra .= ' ' . $k . '="' . blade_escape_special_chars((string)$v) . '"';
        }
    }

    if ($options === null && $values === null) {
        return '';
    }

    $html = '';
    if ($options !== null) {
        foreach ($options as $key => $val) {
            $html .= _blade_html_options_optoutput($key, $val, $selected);
        }
    } else {
        foreach ($values as $i => $key) {
            $val  = $output[$i] ?? '';
            $html .= _blade_html_options_optoutput($key, $val, $selected);
        }
    }

    if ($name !== null) {
        $html = '<select name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"' . $extra . ">\n" . $html . "</select>\n";
    }

    return $html;
}

function _blade_html_options_optoutput($key, $value, array $selected): string {
    if (!is_array($value)) {
        $sel = '';
        foreach ($selected as $s) {
            if ((string)$s === (string)$key) {
                $sel = ' selected="selected"';
                break;
            }
        }
        return '<option label="' . blade_escape_special_chars((string)$value) . '"'
             . ' value="' . blade_escape_special_chars((string)$key) . '"'
             . $sel . '>'
             . blade_escape_special_chars((string)$value) . "</option>\n";
    }
    // optgroup
    $html = '<optgroup label="' . blade_escape_special_chars((string)$key) . "\">\n";
    foreach ($value as $k => $v) {
        $html .= _blade_html_options_optoutput($k, $v, $selected);
    }
    $html .= "</optgroup>\n";
    return $html;
}

// -----------------------------------------------------------------------
// {html_radios} 相当
// -----------------------------------------------------------------------

/**
 * ラジオボタン群を生成する
 * Smarty の {html_radios} タグ相当
 *
 * @param array $params
 *   - name      : input の name 属性（デフォルト "radio"）
 *   - options   : 連想配列 [value => label]
 *   - values    : 値の配列
 *   - output    : 表示テキストの配列
 *   - selected  : 選択値
 *   - separator : 各ラジオボタン間の区切り文字列
 *   - style     : style 属性
 * @return string
 */
function blade_html_radios(array $params): string {
    $name      = $params['name']      ?? 'radio';
    $options   = isset($params['options']) ? (array)$params['options'] : null;
    $values    = isset($params['values'])  ? array_values((array)$params['values']) : null;
    $output    = isset($params['output'])  ? array_values((array)$params['output']) : null;
    $selected  = isset($params['selected']) ? (string)$params['selected'] : null;
    $separator = $params['separator'] ?? '';
    $style     = isset($params['style']) ? ' style="' . htmlspecialchars($params['style'], ENT_QUOTES, 'UTF-8') . '"' : '';

    // extra 属性
    $extra = '';
    $skip  = ['name', 'options', 'values', 'output', 'selected', 'separator', 'style', 'labels'];
    foreach ($params as $k => $v) {
        if (!in_array($k, $skip) && !is_array($v)) {
            $extra .= ' ' . $k . '="' . blade_escape_special_chars((string)$v) . '"';
        }
    }

    if ($options === null && $values === null) {
        return '';
    }

    $items = [];
    if ($options !== null) {
        foreach ($options as $val => $label) {
            $items[] = [$val, $label];
        }
    } else {
        foreach ($values as $i => $val) {
            $items[] = [$val, $output[$i] ?? ''];
        }
    }

    $html = '';
    foreach ($items as [$val, $label]) {
        $checked = ($selected !== null && (string)$selected === (string)$val) ? ' checked="checked"' : '';
        $id      = htmlspecialchars($name . '_' . $val, ENT_QUOTES, 'UTF-8');
        $html   .= '<label for="' . $id . '"' . $style . '>'
                 . '<input type="radio" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"'
                 . ' id="' . $id . '"'
                 . ' value="' . blade_escape_special_chars((string)$val) . '"'
                 . $checked . $extra . '>'
                 . blade_escape_special_chars((string)$label)
                 . '</label>'
                 . $separator;
    }

    return $html;
}

// -----------------------------------------------------------------------
// {html_checkboxes} 相当
// -----------------------------------------------------------------------

/**
 * チェックボックス群を生成する
 * Smarty の {html_checkboxes} タグ相当
 *
 * @param array $params
 *   - name      : input の name 属性（デフォルト "checkbox"）
 *   - options   : 連想配列 [value => label]
 *   - values    : 値の配列
 *   - output    : 表示テキストの配列
 *   - checked / selected : チェックする値（配列可）
 *   - separator : 各チェックボックス間の区切り文字列
 * @return string
 */
function blade_html_checkboxes(array $params): string {
    $name      = $params['name']      ?? 'checkbox';
    $options   = isset($params['options']) ? (array)$params['options'] : null;
    $values    = isset($params['values'])  ? array_values((array)$params['values']) : null;
    $output    = isset($params['output'])  ? array_values((array)$params['output']) : null;
    $selected  = isset($params['checked'])
        ? array_map('strval', array_values((array)$params['checked']))
        : (isset($params['selected'])
            ? array_map('strval', array_values((array)$params['selected']))
            : []);
    $separator = $params['separator'] ?? '';

    // extra 属性
    $extra = '';
    $skip  = ['name', 'options', 'values', 'output', 'checked', 'selected', 'separator', 'labels',
               'break_separator', 'break_separator_interval'];
    foreach ($params as $k => $v) {
        if (!in_array($k, $skip) && !is_array($v)) {
            $extra .= ' ' . $k . '="' . blade_escape_special_chars((string)$v) . '"';
        }
    }

    if ($options === null && $values === null) {
        return '';
    }

    $items = [];
    if ($options !== null) {
        foreach ($options as $val => $label) {
            $items[] = [$val, $label];
        }
    } else {
        foreach ($values as $i => $val) {
            $items[] = [$val, $output[$i] ?? ''];
        }
    }

    $html = '';
    foreach ($items as [$val, $label]) {
        $checked = in_array((string)$val, $selected, true) ? ' checked="checked"' : '';
        $id      = htmlspecialchars($name . '_' . $val, ENT_QUOTES, 'UTF-8');
        $html   .= '<label for="' . $id . '">'
                 . '<input type="checkbox" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '[]"'
                 . ' id="' . $id . '"'
                 . ' value="' . blade_escape_special_chars((string)$val) . '"'
                 . $checked . $extra . '>'
                 . blade_escape_special_chars((string)$label)
                 . '</label>'
                 . $separator;
    }

    return $html;
}

// -----------------------------------------------------------------------
// |date_format 相当
// -----------------------------------------------------------------------

/**
 * 日付を指定フォーマットで整形する
 * Smarty の |date_format modifier 相当（日本語曜日対応）
 */
function blade_date_format(?string $string, string $format = '%Y/%m/%d', ?string $default_date = null): string {
    // ミリ秒除去（元プラグインの K-Cre MOD 相当）
    if ($string !== null && str_contains($string, '.')) {
        [$string] = explode('.', $string);
    }

    $src = ($string !== '' && $string !== null) ? $string : $default_date;
    if ($src === null || $src === '') {
        return '';
    }

    $ts = strtotime($src);
    if ($ts === false) {
        return '';
    }

    // %k → 漢字曜日1文字
    if (str_contains($format, '%k')) {
        $map = ['Monday' => '月', 'Tuesday' => '火', 'Wednesday' => '水',
                'Thursday' => '木', 'Friday' => '金', 'Saturday' => '土', 'Sunday' => '日'];
        $format = str_replace('%k', $map[date('l', $ts)] ?? '', $format);
    }

    // PHP 8.1+ では strftime() が非推奨のため date() で代替
    $format = str_replace(
        ['%Y', '%m', '%d', '%H', '%M', '%S', '%j', '%e', '%n', '%t', '%T', '%D'],
        ['Y',  'm',  'd',  'H',  'i',  's',  'z',  'j',  "\n", "\t", 'H:i:s', 'm/d/y'],
        $format
    );

    return date($format, $ts);
}

// -----------------------------------------------------------------------
// |mb_truncate 相当
// -----------------------------------------------------------------------

/**
 * マルチバイト対応文字列切り詰め
 * Smarty の |mb_truncate modifier 相当
 */
function blade_mb_truncate(?string $string, int $length = 80, string $etc = '...', bool $break_words = false): string {
    if ($string === null || $string === '') {
        return '';
    }
    if (mb_strlen($string) <= $length) {
        return $string;
    }
    if (!$break_words) {
        // 単語境界で切る（日本語では全文字単語扱い）
        return mb_substr($string, 0, $length) . $etc;
    }
    return mb_substr($string, 0, $length) . $etc;
}

// -----------------------------------------------------------------------
// |mb_truncate_with_tag 相当
// -----------------------------------------------------------------------

/**
 * HTMLタグを考慮したマルチバイト文字列切り詰め
 * Smarty の |mb_truncate_with_tag modifier 相当
 */
function blade_mb_truncate_with_tag(?string $string, int $zen_length = 80, string $etc = '…'): string {
    if ($zen_length === 0 || $string === null || $string === '') {
        return '';
    }

    $enc      = defined('_ENCODING_SRC') ? _ENCODING_SRC : 'UTF-8';
    $etc_len  = strlen(mb_convert_encoding($etc, 'sjis-win', $enc));
    $han_max  = $zen_length * 2 - $etc_len;
    $han_len  = 0;
    $etc_added = false;
    $tag_skip = false;
    $ret      = '';

    for ($i = 0; $i < mb_strlen($string, $enc); $i++) {
        $one = mb_substr($string, $i, 1, $enc);

        if ($tag_skip) {
            $ret .= $one;
            if ($one === '>') {
                $tag_skip = false;
            }
        } elseif ($one === '<') {
            $tag_skip = true;
            if ($etc_added) {
                $next4 = strtolower(mb_substr($string, $i, 4, $enc));
                $next5 = strtolower(mb_substr($string, $i, 5, $enc));
                $next6 = strtolower(mb_substr($string, $i, 6, $enc));
                if ($next4 === '<br>') {
                    $i += 3; $tag_skip = false;
                } elseif ($next5 === '<br/>') {
                    $i += 4; $tag_skip = false;
                } elseif ($next6 === '<br />') {
                    $i += 5; $tag_skip = false;
                } else {
                    $ret .= $one;
                }
            } else {
                $ret .= $one;
            }
        } else {
            $one_sjis = mb_convert_encoding($one, 'sjis-win', $enc);
            $han_len += strlen($one_sjis) === 1 ? 1 : 2;
            if ($han_len <= $han_max) {
                $ret .= $one;
            } elseif (!$etc_added) {
                $ret .= $etc;
                $etc_added = true;
            }
        }
    }

    return $ret;
}
