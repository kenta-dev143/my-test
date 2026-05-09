<?php
// 食材データ（本来はDBから取得する内容）
$foods = [
    ['name' => '白米', 'protein' => 2.5],     // 100gあたり2.5g
    ['name' => '食パン', 'protein' => 9.3],   // 100gあたり9.3g
    ['name' => 'キャベツ', 'protein' => 1.3],
    ['name' => '低たんぱく米', 'protein' => 0.1],
];

// 計算処理
$total_protein = 0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_index = $_POST['food_index'];
    $amount = $_POST['amount'];

    // タンパク質計算：(100gあたりの量 * 食べたg) / 100
    $p_per_100 = $foods[$selected_index]['protein'];
    $total_protein = ($p_per_100 * $amount) / 100;
}
?>

<!DOCTYPE html>
<html lang="ja">
<body>
    <h1>食事管理ツール（制限：6g/日）</h1>

    <form method="post">
        <label>食材：</label>
        <select name="food_index">
            <?php foreach ($foods as $index => $food): ?>
                <option value="<?php echo $index; ?>"><?php echo $food['name']; ?> (<?php echo $food['protein']; ?>g/100g)</option>
            <?php endforeach; ?>
        </select>

        <label>食べた量(g)：</label>
        <input type="number" name="amount" required>

        <button type="submit">計算する</button>
    </form>

    <?php if ($total_protein > 0): ?>
        <h2>計算結果</h2>
        <p>摂取タンパク質：<strong><?php echo number_format($total_protein, 2); ?> g</strong></p>
        <p>残りの許容量：<?php echo number_format(6 - $total_protein, 2); ?> g</p>
    <?php endif; ?>
</body>
</html>