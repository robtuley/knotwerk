Provides a simple DB interface. 3 database types are directly supported (MySQL, SQLite and PostgreSQL) and the package is based on PDO so using other DBs is not difficult. Connections are always managed as a master-slave pair (even if these are actually the same) to ease future scalability of your application.

== Creating a Connection ==

Database connections are always managed in master-slave pairs. Initailly these connections are likely to be one and the same for your application, and the T_Pdo_Single class manages a single master-slave connection. It takes as an argument the connection object which depends on your DB type.

<?php
// MySQL connect
$conn = new T_Mysql_Connection('host','user',
                               'passwd','db_name');
$db = new T_Pdo_Single($conn);

// SQLite connect
$conn = new T_Sqlite_Connection('my/file/name.db');
$db = new T_Pdo_Single($conn);

// PostgreSQL connect
$conn = new T_Postgres_Connection('host','user',
                                  'passwd','db_name');
$db = new T_Pdo_Single($conn);

$db->master(); // returns master connection
$db->slave();  // returns slave connections
?>

No classes are provided to manage a separate master-slave connection, it is expected that if your app grows to this size your requirements will be bespoke enough to write your own master-slave connection handler, the only constraint being that it should implement the T_Db interface.

== Read Queries ==

Read queries (i.e. SELECTs) can be executed on either the master or slave DB connections using the query() method.

<?php
$result = $db->slave()->query('SELECT name,email FROM users');
foreach ($result as $row) {
    // $row['name'] & $row['email'] available
}
?>

=== Prepared Queries ===

The safest way to handle dynamic query parameters is to use prepared queries. Insert question marks in your query string and pass in an array of parameters in the required order as the second argument to the query function.

<?php
$sql = 'SELECT * FROM users WHERE email=?';
$result = $db->slave()->query($sql,array($_POST['email']));
?>

Alternatively you can use named parameters to reference the field data in your prepared query: although more verbose this allows you to reference the same data in more than one place in your query. The placeholder is referenced in the query using a colon followed by the name, and this name must match a named key in the data you pass to the query() method.

<?php
$sql = 'SELECT * FROM relationships '.
       'WHERE parent=:id OR child=:id';
$result = $db->slave()->query($sql,array('id'=>$_POST['id']));
?>

=== Escaping Dynamic Parameters ===

An alternative to using prepared queries is to directly escape the dynamic parameters and add them directly into your query string. The master and slave connections each implement the T_Filter interface, with this filter converting the value passed to it into a valid SQL literal.

<?php
$s = $db->slave();
$sql = 'SELECT * FROM users '.
       'WHERE email='.$s->transform($_POST['email']);
$result = $s->query($sql);
?>

=== Working with Results ===

Executing a read query returns a results object that conforms to the T_Db_Result interface. It can be be iterated over with a foreach, or the fetch() method can be used to get the next row in the resultset. In both cases each row is returned as an associative array.

<?php
$result = $db->slave()->query('SELECT name,email FROM user');

// iterator
foreach ($result as $row) {
    // $row['name'] & $row['email'] available
}

// fetch
while ($row=$result->fetch()) {
    // $row['name'] & $row['email'] available
}

// countable
$num_rows = count($result);

// get all rows as an array
$all = $result->fetchAll();
?>

=== Single Row Retrieval Shortcut ===

The queryAndFetch method is available when a single row result is expected from the query, and will throw a T_Exception_Query if an empty or multiple rowset is returned. This is useful for COUNT(*) or other queries. If the row has only one element it the method will return that scalar element, if the row has multiple elements it will return an associative arry.

<?php
$count = $db->slave()->queryAndFetch('SELECT COUNT(*) FROM users');
  // $count is populated with the integer number of users

$sql = 'SELECT COUNT(*) AS total, COUNT(phone) AS with_phone '.
       'FROM users';
$data = $sb->slave()->queryAndFetch($sql);
  // $data['total'] and $data['with_phone'] are available
?>

== Write Queries ==

Write queries **can only be executed on the master connection** and an exception will be thrown if a query is executed on the slave connection that does not return a resultset. This helps to enforce the usage separation between the two connections. Using the master connections for write queries is exactly the same as read queries except the query() method returns a reference to itself (for possible fluent interface re-use) rather than a result object.

<?php
// safest as prepared query
$sql = 'INSERT INTO users (name,email) '.
       'VALUES (?,?)';
$data = array('Joe Bloggs','joe@example.com');
$db->master()->query($sql,$data);

// ... or escape data into query string directly
$m = $db->master();
$sql = 'INSERT INTO users (name,email) '.
       'VALUES ('.$m->transform('Joe Bloggs').','.
                  $m->transform('joe@example.com').')';
$m->query($sql);
?>

=== Last Insert IDs ===

If data is being inserted into a AUTO_INCREMENT field, the last insert ID can be accessed from the master connection using the method getLastId().

<?php
$sql = 'INSERT INTO users (name,email) '.
       'VALUES (?,?)';
$data = array('Joe Bloggs','joe@example.com');
$id = $db->master()->query($sql,$data)
                   ->getLastId();
    // $id now holds ID for Joe
?>

For PostgreSQL databases, you need to specify which sequence you want to get the last ID from, this can be passed in as an argument to the getLastId() method.

<?php
$id = $db->master()->query($sql,$data)
                   ->getLastId('users_seq_id');
?>

=== Multiple Query Execution ===

Multiple **write** queries can be executed using the master connection load() method. This is regarded as a install script helper rather normal application runtime execution and **will not execution all valid SQL correctly**. PDO does not natively support multiple queries, so this helper simply splits the query text whenever it finds a semi-colon followed by an end-of-line marker, and executes each part in turn. The requirement of an end-of-line means SQL with a semi-colon mid-query can be executed.

~~~~
-- [contents of install.sql for SQLite]
CREATE TABLE users
(
id INTEGER PRIMARY KEY ASC,
name TEXT,
email  TEXT COLLATE NOCASE NOT NULL
);

CREATE TRIGGER fkd_users
BEFORE DELETE ON users
FOR EACH ROW BEGIN
    DELETE FROM users_role WHERE user=OLD.id; -- cascade delete
END;
~~~~

<?php
$db->master()->load(file_get_contents('install.sql'));
?>

Notice how in the above example, the "cascade delete" comment in the trigger is required to prevent a line return following the semi-colon (and thus the load() method splitting the trigger statement into two invalid pieces).

== Transactions ==

Transactions are available on the master connection using the methods begin(), commit() and rollback(). The method isCommitted() can be used to query the connection to test if there is currently open transaction.

<?php
$db->master()->begin()
             ->query('UPDATE ...')
             ->query('UPDATE...')
             ->commit();
?>

== Query Failure ==

Any query or connection errors will result in a T_Exception_Query being thrown. This exception will automatically rollback a transaction if one is open on the master connection.

<?php
try {
    $db->master()->begin()
                 ->query('invalid SQL')
                 ->commit();
} catch (T_Exception_Query $e) {
    // transaction has already been rolled back
}
?>

== Database Type Abstraction ==

This lightweight wrapper does not provide any database abstraction, and expects raw SQL arguments. The library itself supports both MySQL and SQLite by virtue of using SQL that is compatible with either, and there are plans to shortly include PostgreSQL support too. In the cases that there is significant advantage in using DB-specific SQL, the DB type can be explicitally sniffed by using the main connection pair is() method.

<?php
if ($db->is(T_Db::MYSQL)) {
    // MySQL specific code used since it saves the use of a transaction
    // and an additional query.
    $sql = 'INSERT INTO order_code (counter) '.
            "VALUES (LAST_INSERT_ID(1)) ".
            'ON DUPLICATE KEY UPDATE counter=LAST_INSERT_ID(counter+1)';
    $num = $db->master()->query($sql)
                        ->getLastId();
} else {
    $m = $db->master();
    $m->begin();
    $sql = "SELECT counter FROM order_code LIMIT 1";
    $result = $m->query($sql);
    if (count($result)>0) {
        $num = (int) _first($result->fetch());
        $num++;
        $master->query("UPDATE order_code SET counter=$num $where");
    } else {
        $num = 1;
        $sql = 'INSERT INTO order_code (counter) '.
                "VALUES ($num)";
        $m->query($sql);
    }
    $m->commit();
}
?>
