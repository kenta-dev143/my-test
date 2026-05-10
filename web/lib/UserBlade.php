<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/blade_helpers.php';

use eftec\bladeone\BladeOne;

/**
 * BladeOne テンプレートエンジンラッパー
 * UserSmarty と同等のインターフェースを提供する
 */
class UserBlade {

    /** @var BladeOne|null BladeOne インスタンス（遅延初期化） */
    private ?BladeOne $_blade = null;

    /** @var string テンプレートディレクトリ（Smarty の template_dir 互換） */
    public string $template_dir = '';

    /** @var string キャッシュディレクトリ */
    private string $_cache_dir;

    /** @var array assign された変数 */
    private array $_data = [];

    public function __construct() {
        $this->_cache_dir = _ROOT_CACHE_DIR . '/templates_c';
        if (!is_dir($this->_cache_dir)) {
            mkdir($this->_cache_dir, 0777, true);
        }
    }

    /**
     * テンプレート変数を割り当てる（Smarty::assign() 互換）
     */
    public function assign(string $key, $value): void {
        $this->_data[$key] = $value;
    }

    /**
     * 割り当て済み変数を取得する（メールテンプレート用）
     */
    public function getData(): array {
        return $this->_data;
    }

    /**
     * テンプレートをレンダリングして文字列で返す
     * @param string $template テンプレート名（.html/.blade.php 可）
     */
    public function fetch(string $template): string {
        // 絶対パスが渡された場合はベース名のみ取り出す
        if (str_contains($template, '/') || str_contains($template, '\\')) {
            $template = basename($template);
        }
        // 拡張子を除去
        $template = preg_replace('/\.(blade\.php|html)$/', '', $template);
        return $this->_getInstance()->run($template, $this->_data);
    }

    /**
     * テンプレートをレンダリングして出力する
     */
    public function display(string $template): void {
        echo $this->fetch($template);
    }

    /**
     * 文字列テンプレートをレンダリングして返す（メールテンプレート用）
     */
    public function fetchString(string $templateStr): string {
        return $this->_getInstance()->runString($templateStr, $this->_data);
    }

    /**
     * BladeOne インスタンスを取得（template_dir が変わった場合は再生成）
     */
    private function _getInstance(): BladeOne {
        if ($this->_blade === null) {
            $this->_blade = new BladeOne(
                rtrim($this->template_dir, '/\\'),
                $this->_cache_dir,
                BladeOne::MODE_AUTO
            );
        }
        return $this->_blade;
    }
}
