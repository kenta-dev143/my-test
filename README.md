# my-test
1. 【準備】正しい部屋（フォルダ）に入る
Gitは「今いるフォルダ」に対して命令を聞きます。まずは対象のプロジェクトへ移動します。
Bash
# 練習用（my-test）の場合
cd ~/work/haruhi

# 展示会（tenjikai）の場合
cd ~/work/tenjikai

# 「今どこに繋がってる？」と不安になったら確認
git remote -v


2. 【朝のルーティン】正史（main）を最新にする
誰かが進めた時計の針を、自分のMacにも反映させます。これを忘れると後で「衝突（コンフリクト）」が起きます。
Bash
# 1. 共通の正史（main）に戻る
git checkout main

# 2. GitHubから最新状態をダウンロードして同期
git pull origin main


3. 【作業開始】自分専用の「外伝（枝）」を作る
本編（main）を汚さないよう、自分だけの作業スペースを切り出します。
Bash
# 「feature/作業内容」という名前で新しい枝を作って移動
git checkout -b feature/add-logic-0510

💡 納得ポイント: -b は「新しく作る（build）」の頭文字。これ以降の作業は main には影響しません。

4. 【作業中】変更を記録して、GitHubへ送る
キリの良いところで、変更をクラウド（GitHub）へ保存します。
Bash
# 1. 変更したファイルをすべて「保存ボックス」へ（荷造り）
git add .

# 2. 何をしたかメモをつけて確定（封印）
git commit -m "〇〇機能を実装"

# 3. 自分の枝をGitHubへ送り出す（発送）
git push origin feature/作業したブランチ名


5. 【仕上げ】ブラウザで「査閲（プルリク）」を出す
ここからは「人間同士のやり取り」なので、GitHubの画面で行います。
黄色いバーの Compare & pull request を押す。
タイトル・内容（「〜を実装しました。確認お願いします」）を書いて、Create pull request を押す。
査読（レビュー）を待ち、OKが出たら Merge pull request を押して main へ合流させる。

「pull」は同期: 相手から貰う。
「push」は送信: 相手に送る。
「checkout -b」は分身: 本番を守るために、別の自分を作る。
「add/commit」は保存: 今の状態を記録する。


