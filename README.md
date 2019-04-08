WordPress上でWebRTCを使うことができます。


多人数で ビデオ通話と簡単なテキストチャットができます。

設定でWebRTCはSFUモードかmeshモード使用できます。
多人数の場合はSFUモードだと下りをサーバー経由で一本化してくれるので
それほど負荷は増大しないようです。
meshモードはp２pで接続します。


外部サイトからこのプラグインをロードするjavascript。 （https://cdn.webrtc.ecl.ntt.com/skyway-latest.js）
を読み込みます

作成するのにチュートリアルとlizefield氏公開の「Skyway WebRTC」
チュートリアルを参考に作成した位ですので
WebRTC技術及びskywayサービスに詳しくありません。

使い方

管理画面で
wordpress のフォルダ「plugins」の中に
フォルダ「lite-skyway-webrtc」毎コピーします。

有効にしたら、
「Skyway WebRTC」の設定

API Key	とSFU roomを使うかのチェックあるので入力します。

API Keyは予め
https://console-webrtc-free.ecl.ntt.com/users/login
から「+ Create new Applications」でアプリケーション追加

Application description :　任意の説明
Available domains : 使うドメイン名


Enabled TURN : チェック(既定値)
Enabled SFU: チェック(既定値) プラグインの設定でmeshモードでも使えます。
Enabled listAllPeers API:チェック(既定値)　
Enabled API Key authentication チェックしない(既定値)


使いたい投稿記事または固定ページに`[SKYWAY_ROOM]` タグを置きます。



* このプラグインはSkywayの公式ではありません。
* このソフトウェアには、Apache License 2.0のライセンスとなります

-----

You can use WebRTC simply on WordPress.
This WebRTC is SFU mode or mesu mode .

This plugin loading javascript from external site. (https://cdn.webrtc.ecl.ntt.com/skyway-latest.js)
Because it’s need to webrtc.
This plugin is not Skyway official.
This software includes the work that is distributed in the Apache License 2.0.

1. Go to  `https://webrtc.ecl.ntt.com/` and create account.  
2. Create new application at Skyway.  
3. Registor your wordpress site and enable SFU option at Skyway.  
4.You can select SFU mode or MESH mode in the plug-in setting
5. Get your API Key from Skyway.  
6. Go to admin menu Skyway WebRTC.  
6. Input your API Key.  
7. Write `[SKYWAY_ROOM]` any place.
8. Access that page, then you start WebRTC.
