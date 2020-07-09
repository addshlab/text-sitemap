<?php
/*
Plugin Name: Text Sitemap
Plugin URI: add.sh
Description: sitemap.txtを生成します。
Author: add.sh
Version: 0.1
*/

new textSitemap();

class textSitemap {

    private const SITEMAP_FILE = ABSPATH . 'sitemap.txt';

    public function __construct() {
        # 投稿の状態が変わった時にサイトマップの更新を行う
        add_action( 'transition_post_status', array( $this, 'generate_file') );
        # オプションページ
        add_action( 'admin_menu', array( $this, 'sitemap_settings_menu' ) );
        # オプションページの項目
        add_action( 'admin_init', array( $this, 'sitemap_settings_api_init') );
        # サイトマップの手動生成
        add_action( 'updated_option', array( $this, 'manual_generate_file' ) );
    }

    /**
     * サイトマップ用URLの生成
     */
    public function post_query( ) {
        global $wpdb;

        $options = get_option( 'textsitemap_post_type' ) ;
        foreach( $options as $option ) {
            $selected_post_status .= "'" . $option . "'";
            if ( $option !== array_key_last( $options) ) {
                $selected_post_status .= ',';
            }
         }

        $query = "
SELECT * FROM $wpdb->posts
WHERE post_type IN ($selected_post_status)
AND post_status='publish';
        ";
        $rows = $wpdb->get_results($query);
        foreach( $rows as $row ) {
            $permalink[] = get_permalink( $row -> ID ) . "\n";
        }
        return $permalink;
    }

    /**
     * サイトマップファイルの生成
     */
    public function generate_file () {
        file_put_contents( self::SITEMAP_FILE, $this->post_query() );               
    }

    /**
     * サイトマップファイルの存在判定
     */
    private function is_sitemap_file () {
        if ( file_exists( self::SITEMAP_FILE ) ) return true;
        return false;
    }

    /**
     * サイトマップファイルの書き込み可能判定
     */
    private function is_sitemap_writable() {
        if ( is_writable( self::SITEMAP_FILE ) ) return true;
        return false;
    }

    /**
     * サイトマップのファイルサイズ
     */
    private function get_sitemap_size() {
        if ( ! $this->is_sitemap_file() ) return false;
        return filesize( self::SITEMAP_FILE );
    }

    /**
     * サイトマップの行数
     */
    private function get_sitemap_lines() {
        if ( ! $this->is_sitemap_file() ) return false;
        return exec( 'wc -l ' . self::SITEMAP_FILE . " | awk '{print $1}'");
    }

    /**
     * バイト数の単位変換
     * @see https://qiita.com/git6_com/items/ecaafb1afb42fc207814
     */
    private function prettyByte2Str( $bytes ) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

    /**
     * 設定画面にサイトマップの状態を表示
     */
    public function textsitemap_message () {
        if ( $this->is_sitemap_file() ) {
            $sitemap_url  = '<a href="' . esc_url( home_url( '/' ) ) . 'sitemap.txt">' . esc_url( home_url( '/' ) ) . 'sitemap.txt</a>';
            $sitemap_path = self::SITEMAP_FILE;
        } else {
            $sitemap_url  = 'なし';
            $sitemap_path = 'なし';
        }

        if ( $this->is_sitemap_writable() ) {
            $writable     = 'あり'; 
        } else {
            $writable     = 'なし'; 
        }

        echo '<h2>サイトマップファイル情報</h2>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th class="row">サイトマップ</th>';
        echo '<td>' . $sitemap_url . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th class="row">サーバ内パス</th>';
        echo '<td>' . $sitemap_path . '</td>';
        echo '</tr>'; 
        echo '<tr>';
        echo '<th class="row">書き込み権限</th>';
        echo '<td>' . $writable . '</td>';
        echo '</tr>'; 
        echo '<tr>';
        echo '<th class="row">ファイルサイズ</th>';
        echo '<td>' . $this->prettyByte2Str( $this->get_sitemap_size() ) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th class="row">ファイル行数</th>';
        echo '<td>' . $this->get_sitemap_lines() .'</td>';
        echo '</tr>'; 
        echo '</table>';

    }

    /**
     * サイトマップの手動生成
     */
    public function manual_generate_file() {
        if ( ! $_POST['textsitemap_generate'] ) return;
        $this->generate_file();
    }

    /**
     * オプションページ
     */
    public function sitemap_settings_menu() {
        add_options_page(
            'Text Sitemap', // ページのタイトル
            'Text Sitemap', // メニューのタイトル
            'manage_options', // このページを操作する権限
            'textsitemap_settings', // ページ名
            array( $this, 'sitemap_settings_plugin_options' ), // コールバック関数。この関数の実行結果が出力される
        );
    }

    public function sitemap_settings_plugin_options() { ?>
        <div class="wrap">
           <form action="options.php" method="post">
            <?php settings_fields('sitemap_settings-group'); // グループ名 ?>
            <?php do_settings_sections( 'textsitemap_settings'); // ページ名 ?>
            <?php submit_button(); ?>
        </form>
    </div> <?php
    }

    /**
     * オプションメニュー
     * @see https://wpdocs.osdn.jp/Settings_API
     */
    public function sitemap_settings_api_init() {
        add_settings_section(
            'sitemap_setting_section',
            'Text Sitemap Settings',
            array( $this, 'textsitemap_section_callback' ),
            'textsitemap_settings',
        );
        
        # 投稿タイプ一覧
        add_settings_field(
            'textsitemap_all_post_type',
            '投稿タイプ一覧',
            array ( $this, 'textsitemap_all_post_type_field_callback' ),
            'textsitemap_settings',
            'sitemap_setting_section',
        );

        # sitemap.txt 手動生成
        add_settings_field(
            'textsitemap_generate',
            'sitemap.txtを手動生成する',
            array ( $this, 'textsitemap_generate_field_callback' ),
            'textsitemap_settings',
            'sitemap_setting_section',
        );
 
        
        register_setting(
            'sitemap_settings-group',
            'textsitemap_post_type',
        );

        register_setting(
            'sitemap_settings-group',
            'textsitemap_generate',
        );
    }

    public function textsitemap_section_callback() {
        $this->textsitemap_message();
        echo '<h3>サイトマップ設定</h3>';
    } 
 
    public function textsitemap_all_post_type_field_callback( $args ) {
        $post_types = get_post_types( '', 'names' );
        foreach( $post_types as $post_type ) {
            $output .= '<p><input name="textsitemap_post_type['. $post_type .']" id="textsitemap_' . $post_type . '" type="checkbox" value="' . $post_type . '" class="code"' . checked( $post_type, get_option( 'textsitemap_post_type' )[$post_type], false ) . ' />';
            $output .= '<label for="textsitemap_' . $post_type . '">' . $post_type . '</label></p>';
        }
        echo $output;
    }

    public function textsitemap_generate_field_callback() {
        $output = '<p><input class="code" name="textsitemap_generate" id="textsitemap_generate" type="checkbox" value="' . date( 'U' ) . '" />';
        $output .= '<label for="textsitemap_generate">今回の設定保存時に手動生成する</label></p>';
        echo $output;
    }

}
