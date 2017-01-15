<form method="post">
    <label>
        Title:<br/>
        <input type="text" name="title">
    </label>
    <br/>
    <label>
        Content:<br/>
        <textarea rows="5" cols="20" name="content"></textarea>
    </label>
    <br/>
    <button type="submit">Save</button>
</form>

<form method="get">
    <label>
        Search by title:<br/>
        <input type="search" name="filter" />
    </label>
    <button type="submit">Filter</button>
</form>

<?php

//phpinfo();

try {
    // Подключение к б/д
    //$dsn = "mysql:host=localhost;dbname=blog";
    $dsn = "sqlite:blog.sqlite";
    $db = new PDO($dsn, 'blog', 'blog');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color: green'>connected</p>";

//    $sql = "CREATE TABLE post (
//              id integer PRIMARY KEY AUTOINCREMENT,
//              title VARCHAR(60) NOT NULL,
//              content TEXT NOT NULL,
//              published_date TEXT NOT NULL
//            )";
//    $db->exec($sql);


    $db->beginTransaction(); // начало транзакции
    if(!empty($_POST)) {
        if(isset($_POST['title']) && isset($_POST['content'])) {
            // Запросы без ответа (INSERT, CREATE, DELETE, DROP,...)
//            $title = $_POST['title'];
//            $content = $_POST['content'];
            extract($_POST);
//            echo "<pre>";
//            print_r($GLOBALS);
//            echo "</pre>";
            $published_date = date('Y-m-d H:i:s');
            $db->exec("INSERT INTO post(title, content, published_date) values ('$title', '$content', '$published_date')");
            //throw new PDOException("Unknown error"); // проблема!!!
        }
    }
    $db->commit(); // подтверждаем выполенение команды

    class Row
    {
    }

    if(empty($_GET)) {
        $sql = "SELECT * FROM post ORDER BY published_date DESC";
        $pst = $db->prepare($sql);
        $pst->execute();
    } else {
        $filter = "%{$_GET['filter']}%";
        //$sql = "SELECT * FROM post WHERE title LIKE '%$filter%' ORDER BY published_date DESC";
        $sql = "SELECT * FROM post WHERE title LIKE :filter ORDER BY published_date DESC";
        // Поготовленные запросы
        $pst = $db->prepare($sql); // PDOStatment
        $pst->bindParam(':filter', $filter);
        //$pst->execute(['filter' => "%$filter%"]);
        $pst->execute();
    }

    echo "<pre>";
    echo $sql;
    echo "</pre>";
    // ';set password=password('hack');'
    // Запросы с ответом (SELECT)
    //foreach($db->query($sql, PDO::FETCH_CLASS, 'Row') as $row) {
    foreach ($pst->fetchAll(PDO::FETCH_CLASS, 'Row') as $row){
        echo "<article>";
        echo "<header>";
        echo "<h3> {$row->title} </h3>";
        echo "</header>";
        echo "<div> {$row->content} </div>";
        echo "<footer><span style='font-size: 80%'>Published date: {$row->published_date}</span></footer>";
        echo "</article>";
//        echo "<pre>";
//        print_r($row);
//        echo "</pre>";
    }

    // Транзакции

    // Обработка ошибок
} catch (PDOException $ex) {
    $db->rollBack(); // откат изменений
    echo "<p style='color: red'>" . $ex->getMessage() . "</p>";
}