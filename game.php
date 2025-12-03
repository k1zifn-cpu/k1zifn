<?php
// ======================================================
//        SIMPLE DATABASE FILE (database.json)
// ======================================================

$DB_FILE = "database.json";

if (!file_exists($DB_FILE)) {
    file_put_contents($DB_FILE, json_encode([]));
}

$db = json_decode(file_get_contents($DB_FILE), true);

// ----------------------------
// API: SAVE
// ----------------------------
if (isset($_POST["save"])) {
    $tg_id = $_POST["tg_id"];
    $money = $_POST["money"];
    $slots = $_POST["slots"];

    $db[$tg_id] = [
        "money" => $money,
        "slots" => $slots
    ];

    file_put_contents($DB_FILE, json_encode($db, JSON_PRETTY_PRINT));
    echo "ok";
    exit;
}

// ----------------------------
// API: LOAD
// ----------------------------
if (isset($_GET["load"])) {
    $tg_id = $_GET["tg_id"];

    if (!isset($db[$tg_id])) {
        echo json_encode(["new" => true]);
    } else {
        echo json_encode($db[$tg_id]);
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Telegram Game</title>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<style>
    body {
        background: #0e0e0e;
        font-family: Arial, sans-serif;
        color: #fff;
        margin: 0;
        padding: 20px;
        text-align: center;
    }
    #money {
        font-size: 25px;
        margin-bottom: 15px;
    }
    #grid {
        display: grid;
        grid-template-columns: repeat(4, 70px);
        gap: 10px;
        justify-content: center;
        margin: 20px auto;
    }
    .slot {
        width: 70px;
        height: 70px;
        background: #1c1c1c;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        border: 1px solid #333;
    }
    button {
        background: #1d90f5;
        border: none;
        padding: 15px 25px;
        border-radius: 10px;
        color: white;
        font-size: 18px;
        cursor: pointer;
    }
</style>
</head>
<body>

<h1>🎁 Gift Merge Game</h1>
<div id="money">Загрузка...</div>

<div id="grid"></div>

<button onclick="buyGift()">Купить подарок (1 монета)</button>

<script>
const tg = window.Telegram.WebApp;
tg.expand();

const userId = tg.initDataUnsafe.user.id;

let data = {
    money: 100,
    slots: Array(16).fill(null)
};

function render() {
    document.getElementById("money").innerText = "Монеты: " + data.money;

    let grid = document.getElementById("grid");
    grid.innerHTML = "";

    data.slots.forEach((item, i) => {
        let div = document.createElement("div");
        div.className = "slot";
        div.textContent = item ? item : "";
        grid.appendChild(div);
    });
}

// ======================
// Загрузка с сервера
// ======================
fetch("?load=1&tg_id=" + userId)
    .then(r => r.json())
    .then(res => {
        if (!res.new) {
            data.money = parseInt(res.money);
            data.slots = JSON.parse(res.slots);
        }
        render();
    });

function save() {
    fetch("", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "save=1&tg_id=" + userId +
              "&money=" + data.money +
              "&slots=" + encodeURIComponent(JSON.stringify(data.slots))
    });
}

// ======================
// Покупка подарка
// ======================
function buyGift() {
    if (data.money < 1) {
        alert("Недостаточно монет!");
        return;
    }
    let free = data.slots.indexOf(null);
    if (free === -1) {
        alert("Нет места!");
        return;
    }

    data.money -= 1;

    const gifts = ["🎁", "🎀", "🐻", "❤️", "🌹", "🎂", "💐", "🍾", "🚀", "💍", "💎"];
    const randomGift = gifts[Math.floor(Math.random() * gifts.length)];
    data.slots[free] = randomGift;

    save();
    render();
}
</script>

</body>
</html>
