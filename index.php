<?php

// Подключение к бд
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test_base";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$selectedGroup = isset($_GET['group']) ? $_GET['group'] : 0;

// Функция для создания списка групп товаров
function printGroups($conn, $parentId = 0, $selectedGroup = 0)
{
    $sql = "SELECT g.id, g.name, COUNT(p.id) AS total
            FROM groups g
            LEFT JOIN products p ON g.id = p.id_group
            WHERE g.id_parent = $parentId
            GROUP BY g.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<ul>";

        while ($row = $result->fetch_assoc()) {
            $groupId = $row["id"];
            $groupName = $row["name"];
            $totalProducts = getTotalProducts($conn, $groupId);

            $activeClass = "";
            if ($groupId == $selectedGroup) {
                $activeClass = "active";
            }

            echo "<li class='$activeClass'>";
            echo "<a href='?group=$groupId'>$groupName ($totalProducts)</a>";

            // Проверяем, является ли текущая группа активной
            if ($groupId == $selectedGroup) {
                // Выводим подгруппы текущей группы
                printGroups($conn, $groupId, $selectedGroup);
            }

            echo "</li>";
        }

        echo "</ul>";
    }
}

// Функция для получения общего количества товаров в группе и всех ее подгруппах
function getTotalProducts($conn, $groupId)
{
    $sql = "WITH RECURSIVE group_hierarchy AS (
                SELECT id, id_parent
                FROM groups
                WHERE id = $groupId
                UNION ALL
                SELECT g2.id, g2.id_parent
                FROM groups g2
                JOIN group_hierarchy gh ON g2.id_parent = gh.id
            )
            SELECT COUNT(p.id) AS total
            FROM group_hierarchy gh
            LEFT JOIN products p ON gh.id = p.id_group";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["total"];
    } else {
        return 0;
    }
}

// Функция для вывода всех товаров
function printAllProducts($conn)
{
    if (isset($_GET['group'])) {
        return; // Если выбрана группа, все товары не показываются
    }

    $sql = "SELECT name FROM products";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h2>Все товары:</h2>";
        echo "<ul>";

        while ($row = $result->fetch_assoc()) {
            $productName = $row["name"];
            echo "<li>$productName</li>";
        }

        echo "</ul>";
    }
}

// Вывод всех товаров
printAllProducts($conn);

// Вывод списка групп товаров
printGroups($conn, 0, $selectedGroup);

// Если выбрана группа товаров
if ($selectedGroup != 0) {
    $sql = "SELECT g.id, g.name, COUNT(p.id) AS total
            FROM groups g
            LEFT JOIN products p ON g.id = p.id_group
            WHERE g.id_parent = $selectedGroup
            GROUP BY g.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Вывод выбранной подгруппы
        $selectedGroupName = "";
        
        $sql = "SELECT name
                FROM groups
                WHERE id = $selectedGroup";
        $groupNameResult = $conn->query($sql);

        if ($groupNameResult->num_rows > 0) {
            $selectedGroupRow = $groupNameResult->fetch_assoc();
            $selectedGroupName = $selectedGroupRow["name"];
        }
        
        echo "<h2>$selectedGroupName</h2>";
        echo "<h3>Подгруппы:</h3>"; 
        echo "<ul>";

        while ($row = $result->fetch_assoc()) {
            $subGroupId = $row["id"];
            $subGroupName = $row["name"];
            $subGroupTotalProducts = getTotalProducts($conn, $subGroupId); 

            echo "<li>";
            echo "<a href='?group=$subGroupId'>$subGroupName ($subGroupTotalProducts)</a>";
            echo "</li>";
        }

        echo "</ul>";
    }

    $sql = "SELECT p.name
            FROM products p
            WHERE p.id_group IN (
                WITH RECURSIVE group_hierarchy AS (
                    SELECT id, id_parent
                    FROM groups
                    WHERE id = $selectedGroup

                    UNION ALL
                    SELECT g2.id, g2.id_parent
                    FROM groups g2
                    JOIN group_hierarchy gh ON g2.id_parent = gh.id
                )
                SELECT gh.id FROM group_hierarchy gh
            )";
    $result = $conn->query($sql);


    if ($result->num_rows > 0) {
        echo "<h2>Товары:</h2>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            $productName = $row["name"];
            echo "<li>$productName</li>";
        }
        echo "</ul>";
    }
    
    

    if (isset($_POST['allProducts'])) {
        printAllProducts($conn);
    }
}
$conn->close();
?>