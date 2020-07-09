<?php
/**
 * このファイルはプラグインのアンインストール時に自動実行され
 * このプラグイン由来の設定値をWordPressから削除します。
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

# サイトマップに反映する投稿タイプの設定を削除 
delete_option( 'textsitemap_post_type' );
# サイトマップの手動生成フラグを削除
delete_option( 'textsitemap_generate' );
