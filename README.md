# doc-management-app

適当な場所にクローンします。  
[]内はカレントディレクトリとなります。

#### 初回のみ  
* 環境設定ファイルコピー
```
[/***/doc-sys-api]
$ cp .env-example .env
```

```
[/***/doc-sys-api/src]
$ cd src
$ cp .env.example .env
```

* コンテナの起動
```
[/***/doc-sys-api]
$ cd ..
$ docker-compose up -d --build
```
* login doc-management-app（ashコマンド）。
```
$ docker-compose exec doc-management-app ash
```
* コンポーザインストール
```
[/***/doc-sys-api]
$ docker-compose exec doc-management-app composer install
```
* jwtシークレットを生成する

```
$ docker-compose exec doc-management-app php artisan jwt:secret
```

開発ソースはsrcフォルダ直下にあります。

URL：http://127.0.0.1:10080

#### 2回目以降

* doc-sys-api container
```
[/***/doc-sys-api]
$ docker-compose up -d
```

* start.shでも可  
※必ずプロジェクト直下で実行する。
```
[/***/doc-sys-api]
$ sh start.sh
```

#### 終了する場合

* doc-sys-api container
```
[/***/doc-sys-api]
$ docker-compose down
```

#### イメージを全部削除する

* api container
```
[/***/doc-sys-api]
$ docker-compose down --volumes --rmi all
```

#### DB接続

* MySQL Workbench等のツールでローカルからDBにアクセスする  
ツールに以下の情報を設定する。

| 項目 | 設定値 |
| --- | --- |
| Host | 127.0.0.1 |
| Port | 13308 |
| User | admin-doc |
| Pass | secret |

#### php artisanを実行する

* docker-compose
```
[/***/doc-sys-api]
$ docker-compose exec doc-management-app php artisan config:cache
```

* ash
```
[/***/doc-sys-api]
$ docker-compose exec doc-management-app ash
[/work/doc-management]
$ php artisan migrate
```
