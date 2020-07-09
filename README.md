![NO SUPPORT](http://add.sh/images/no-support.png) ![NO GUARANTEE](http://add.sh/images/no-guarantee.png)

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

# Text Sitemap :: Generate text file sitemap for WordPress

## 注意

このソフトウェアはサポート無し・動作保証無しです。第三者からの要望は受け付けていません。

## 説明

WordPress の記事更新時に、sitemap.txt を生成するプラグインです。


## 仕組み

1. 記事更新時に、WordPressのインストールディレクトリに sitemap.txt を出力

## 仕様

* sitemap.text に、設定画面で選択した投稿タイプのうち、公開済みの投稿の URL を出力する
* 管理画面の設定 -> Text Sitemap に設定画面あり
* 設定画面では、サイトマップに出力する投稿タイプの選択と、サイトマップの手動生成が可能

## なぜ XML ではなくテキストファイルなのか

* URL だけを列挙するだけなので軽量かつ明瞭なため
* Google がサイトをクロールする起点になりさえすればファイル形式は XML に限らないため
* XML や RSS はタグや属性値など必要以上の情報が含まれるため
* XML サイトマップに含まれる URL 以外の情報を重視しているとは思えないため(個人的な意見)


